<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Upload extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'upload
                            {local : Local Path}
                            {remote : Remote Path}
                            {--chuck=5242880 : Chuck Size(byte) }';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Upload File';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        // todo: 文件夹上传
        $local = $this->argument('local');
        $remote = $this->argument('remote');
        $chuck = $this->option('chuck');
        $file_size = OneDrive::readFileSize($local);
        if ($file_size < 4194304) {
            return $this->upload($local, $remote);
        } else {
            return $this->uploadBySession($local, $remote, $chuck);
        }
    }

    /**
     * @param $local
     * @param $remote
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload($local, $remote)
    {
        $content = file_get_contents($local);
        $file_name = basename($local);
        $graphPath = Tool::getRequestPath($remote.$file_name);
        $result = OneDrive::uploadByPath($graphPath, $content);
        $response = OneDrive::responseToArray($result);
        $response['code'] === 200 ? $this->info('Upload Success!')
            : $this->warn('Failed!');
    }

    /**
     * @param     $local
     * @param     $remote
     * @param int $chuck
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadBySession($local, $remote, $chuck = 3276800)
    {
        ini_set('memory_limit', '-1');
        $file_size = OneDrive::readFileSize($local);
        $file_name = basename($local);
        $target_path = Tool::getAbsolutePath($remote);
        $path = trim($target_path, '/') === '' ? ":/{$file_name}:/"
            : Tool::getRequestPath($target_path.$file_name);
        $url_request = OneDrive::createUploadSession($path);
        $url_response = OneDrive::responseToArray($url_request);
        if ($url_response['code'] === 200) {
            $url = array_get($url_response, 'data.uploadUrl');
        } else {
            $this->warn($url_response['msg']);
            exit;
        }
        $this->info("File Path:\n{$local}");
        $this->info("Upload Url:\n{$url}");
        $done = false;
        $offset = 0;
        $length = $chuck;
        while (!$done) {
            $retry = 0;
            $res = OneDrive::uploadToSession($url, $local, $offset, $length);
            $response = OneDrive::responseToArray($res);
            if ($response['code'] === 200) {
                $data = $response['data'];
                if (!empty($data['nextExpectedRanges'])) {
                    $this->info("length: {$data['nextExpectedRanges'][0]}");
                    $ranges = explode('-', $data['nextExpectedRanges'][0]);
                    $offset = intval($ranges[0]);
                    $status = @floor($offset / $file_size * 100).'%';
                    $this->info("success. progress:{$status}");
                    $done = false;
                } elseif (!empty($data['@content.downloadUrl'])
                    || !empty($data['id'])
                ) {
                    $this->info('Upload Success!');
                    $done = true;
                } else {
                    $retry++;
                    if ($retry <= 3) {
                        $this->warn("Retry{$retry}times，Please wait 10s...");
                        sleep(10);
                    } else {
                        $this->warn('Upload Failed!');
                        OneDrive::deleteUploadSession($url);
                        break;
                    }
                }
            } else {
                $this->warn('Upload Failed!');
                OneDrive::deleteUploadSession($url);
                break;
            }
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

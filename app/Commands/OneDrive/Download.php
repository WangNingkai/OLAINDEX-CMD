<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Download extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download {local? : Download Local Path }
                                     {remote? : Download Remote Path}
                                     {--id= : Download Remote File ID}
                                     {--hack : Download Via aria2c}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Download Item';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $remote = $this->argument('remote');
        $local = $this->argument('local');
        $id = $this->option('id');
        $hack = $this->option('hack');
        if (empty($local)) exit('Parameters Missing!');
        if ($id) {
            $result = OneDrive::getItem($id);
        } else {
            if (empty($remote)) exit('Parameters Missing!');
            $graphPath = Tool::getRequestPath($remote);
            $result = OneDrive::getItemByPath($graphPath);
        }
        $response = Tool::handleResponse($result);
        if ($response['code'] === 200) {
            $download = $response['data']['@microsoft.graph.downloadUrl'];
            if (strtolower(PHP_OS) == "winnt") {
                $this->warn("Download Not Support Windows");
                $this->info("Download Link:\n{$download}");
                exit;
            }
            $command = $hack ? "wget --no-check-certificate -c -O --tries=16 {$local} {$download}" : "aria2c -c -o {$local} -s16 -x16 -k1M {$download}";
            $process = new Process($command);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        } else  $this->warn("Failed!\n{$response['msg']} ");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}

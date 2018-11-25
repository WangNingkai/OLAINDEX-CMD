<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Offline extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'offline {remote : Remote Path}
                                    {url : Offline Url}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remote download links to your drive';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $remote = $this->argument('remote');
        $url = $this->argument('url');
        $result = OneDrive::uploadUrl($remote, $url);
        $response = OneDrive::responseToArray($result);
        if ($response['code'] === 200) {
            $redirect = array_get($response, 'data.redirect');
            $this->info('progress link: ' . $redirect);
            $done = false;
            while (!$done) {
                $content = OneDrive::requestUrl('get', $redirect)->getBody()->getContents();
                $result = OneDrive::responseToArray($content);
                $status = array_get($result, 'status');
                if ($status === 'failed') {
                    $this->error(array_get($result, 'error.message'));
                    $done = true;
                } elseif ($status === 'inProgress') {
                    $this->info('Progress: ' . array_get($result, 'percentageComplete'));
                    sleep(3);
                    $done = false;
                } elseif ($status === 'completed') {
                    $this->info('Progress: ' . array_get($result, 'percentageComplete'));
                    $done = true;
                } elseif ($status === 'notStarted') {
                    $this->error('Status:' . $status);
                    $done = false;
                } else {
                    $this->error('Status:' . $status);
                    $done = true;
                }
            }
        } else {
            $this->warn("Failed!\n{$response['msg']} ");
        }
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

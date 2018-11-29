<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use http\Env\Response;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Copy extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'cp
                            {origin : Origin Path}
                            {target : Target Path}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Copy Item';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $this->info('Please waiting...');
        $origin = $this->argument('origin');
        $_origin = OneDrive::responseToArray(OneDrive::pathToItemId(Tool::getRequestPath($origin)));
        $origin_id = $_origin['code'] === 200 ? array_get($_origin, 'data.id') : exit('Origin Path Abnormal');
        $target = $this->argument('target');
        $_target = OneDrive::responseToArray(OneDrive::pathToItemId(Tool::getRequestPath($target)));
        $target_id = $_origin['code'] === 200 ? array_get($_target, 'data.id') : exit('Target Path Abnormal');
        $copy = OneDrive::copy($origin_id, $target_id);
        $response = OneDrive::responseToArray($copy);
        if ($response['code'] === 200) {
            $redirect = array_get($response, 'data.redirect');
            $done = false;
            while (!$done) {
                $result = OneDrive::responseToArray(OneDrive::request('get', $redirect,'',true)->getBody()->getContents());
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

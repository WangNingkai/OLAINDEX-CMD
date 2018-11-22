<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Download extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download {remote? : Remote Path}
                                     {--id= : ID}';

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
        if ($id = $this->option('id')) {
            $result = OneDrive::getItem($id);
        } else {
            $remote = $this->argument('remote');
            if (empty($remote)) exit('Parameters Missing!');
            $graphPath = Tool::getRequestPath($remote);
            $result = OneDrive::getItemByPath($graphPath);
        }
        $response = Tool::handleResponse($result);
        $response['code'] === 200 ? $this->info("Download Link:\n{$response['data']['@microsoft.graph.downloadUrl']}") : $this->warn("Failed!\n{$response['msg']} ");
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

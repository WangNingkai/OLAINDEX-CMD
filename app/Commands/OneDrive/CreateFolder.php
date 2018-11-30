<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CreateFolder extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mkdir
                            {name : Floder Name}
                            {remote : Remote Path}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create A New Folder';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $name = $this->argument('name');
        $remote = $this->argument('remote');
        $graphPath = Tool::getRequestPath($remote);
        $result =  OneDrive::mkdirByPath($name,$graphPath);
        $response = OneDrive::responseToArray($result);
        $this->call('cache:clear');
        $response['code'] === 200 ? $this->info("Folder Created!") : $this->warn("Failed!\n{$response['msg']} ");
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

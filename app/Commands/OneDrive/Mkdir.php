<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Mkdir extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mkdir
                            {name : 文件夹名称}
                            {remote : 远程地址}';

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
        $this->call('refresh:token', ['--quiet' => true]);
        $name = $this->argument('name');
        $remote = $this->argument('remote');
        $graphPath = Tool::getRequestPath($remote);
        $result =  OneDrive::mkdirByPath($name,$graphPath);
        $response = Tool::handleResponse($result);
        $this->call('cache:clear', ['--quiet' => true]);
        $response['code'] == 200 ? $this->info("创建目录成功!") : $this->warn("创建目录失败!\n{$response['msg']} ");
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

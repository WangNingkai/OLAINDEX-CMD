<?php

namespace App\Commands\OneDrive;

use App\Helpers\Constants;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Install extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Install App';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Tool::hasConfig()) {
            $this->warn('开始安装：');
            $app_type = $this->choice('请选择版本(com:国际通用 cn:世纪互联)', ['com', 'cn'], 'com');
            $client_id = $this->ask('请输入 client_id');
            $client_secret = $this->ask('请输入 client_secret');
            $redirect_uri = Constants::REDIRECT_URI;;
            $cache_expires = $this->ask('请输入缓存时间 (min)');;
            $data = [
                'app_type' => $app_type,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'cache_expires' => $cache_expires,
            ];
            $saved = Tool::updateConfig($data);
            $this->call('cache:clear');
            $saved ? $this->info('配置完成！') : $this->warn('配置失败，请稍后重试！');
        } else {
            $this->warn('已配置完成！');
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

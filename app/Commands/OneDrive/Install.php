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
            $this->warn('Starting：');
            if (!file_exists(storage_path('app/config.json'))) {
                copy(storage_path('app/config.sample.json'), storage_path('app/config.json'));
            };
            $app_type = $this->choice('Please choose a version (com:World cn:21Vianet)', ['com', 'cn'], 'com');
            $client_id = $this->ask('client_id');
            $client_secret = $this->ask('client_secret');
            $redirect_uri = $this->ask('redirect_uri',Constants::DEFAULT_REDIRECT_URI);
            $cache_expires = $this->ask('cache expires (min)');
            $data = [
                'app_type' => $app_type,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'cache_expires' => $cache_expires,
            ];
            $saved = Tool::updateConfig($data);
            $this->call('cache:clear');
            $this->call('config:cache');
            $saved ? $this->info('Configuration completed!') : $this->warn('Please try again later.');
        } else {
            $this->warn('Already Configuration completed!');
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

<?php

namespace App\Commands\OneDrive;

use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Logout extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logout {--f|force : Force Logout}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Account Logout';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('force')) return $this->logout();
        if ($this->confirm('Confirm Logout?')) {
            return $this->logout();
        }
    }

    /**
     * Execute Reset Command
     */
    public function logout()
    {
        $data = [
            'access_token' => '',
            'refresh_token' => '',
            'access_token_expires' => 0,
        ];
        $saved = Tool::updateConfig($data);
        if ($saved) {
            $this->call('cache:clear');
            $this->warn('Logout Success!');
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

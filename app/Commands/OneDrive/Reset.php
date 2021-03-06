<?php

namespace App\Commands\OneDrive;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Reset extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'reset {--f|force : Force Reset}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Reset App';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('force')) {
            return $this->reset();
        } else {
            if ($this->confirm('Reset will erase all data, continue?')) {
                return $this->reset();
            }
        }
    }

    /**
     * Execute Reset Command
     */
    public function reset()
    {
        $this->call('cache:clear');
        copy(storage_path('app/config.sample.json'),
            storage_path('app/config.json'));
        $this->info('Reset Completed！');
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

<?php

namespace App\Commands\OneDrive;

use App\Helpers\Constants;
use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class Quota extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'quota';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'OneDrive Info';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('refresh:token');
        $data = Cache::remember('one:quota', Tool::config('cache_expires'), function () {
            $response = OneDrive::getDrive();
            $result = OneDrive::responseToArray($response);
            if ($result['code'] === 200) {
                $quota = array_get($result, 'data.quota');
                foreach ($quota as $k => $item) {
                    if (!is_string($item)) {
                        $quota[$k] = Tool::convertSize($item);
                    }
                }
                return $quota;
            } else {
                return [];
            }
        });
        $headers = array_keys(is_array($data) ? $data : []);
        $quota[] = $data;
        $this->info(Constants::LOGO);
        $this->info('App Version  [' . Tool::config('app_version') . ']');
        $this->table($headers, $quota, 'default');
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

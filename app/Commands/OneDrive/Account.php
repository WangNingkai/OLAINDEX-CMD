<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Account extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'account {--params=*}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Account Info';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $response = Tool::handleResponse(OneDrive::getMe());
        if ($response['code'] === 200) {
            $params = $this->option('params');
            if (!$params) {
                $data = array_get($response, 'data');
            } else {
                $params = explode(',',$params[0]);
                $data = [];
                foreach ($params as $param) {
                    $data[$param] = array_get($response, 'data.' . $param);
                }
            }
            $rows = [];
            foreach ($data as $key => $value) {
                if ($key !== '@odata.context')
                    $rows[] = [$key, is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value];
            }
            $this->table([], $rows);
        } else {
            $this->error('请稍后重试...');
            exit;
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

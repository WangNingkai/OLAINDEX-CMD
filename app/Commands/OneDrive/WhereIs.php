<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class WhereIs extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'whereis {id : Item ID}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Find The Item\'s Remote Path';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $id = $this->argument('id');
        $response = OneDrive::responseToArray(OneDrive::itemIdToPath($id));
        if ($response['code'] === 200) {
            $this->info(array_get($response, 'data.path'));
        } else {
            $this->error($response['msg']);
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

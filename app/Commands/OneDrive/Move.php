<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Move extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mv 
                            {origin : Origin Path}
                            {target : Target Path}
                            {--rename= : Rename}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Move Item';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $this->info('Please waiting...');
        $origin = $this->argument('origin');
        $_origin = OneDrive::responseToArray(OneDrive::pathToItemId(Tool::getRequestPath($origin)));
        $origin_id = $_origin['code'] === 200 ? array_get($_origin, 'data.id') : exit('Origin Path Abnormal');
        $target = $this->argument('target');
        $_target = OneDrive::responseToArray(OneDrive::pathToItemId(Tool::getRequestPath($target)));
        $target_id = $_origin['code'] === 200 ? array_get($_target, 'data.id') : exit('Target Path Abnormal');
        $rename = $this->option('rename') ?? '';
        $move = OneDrive::move($origin_id, $target_id, $rename);
        $response = OneDrive::responseToArray($move);
        $response['code'] === 200 ? $this->info("Move Success!") : $this->warn("Failed!\n{$response['msg']} ");
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

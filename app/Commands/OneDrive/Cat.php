<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Cat extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'cat {remote? : Remote Path}
                                {--id= : Remote File ID}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'View the Text File Content';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $remote = $this->argument('remote');
        $id = $this->option('id');
        if ($id) {
            $result = OneDrive::getItem($id);
        } else {
            if (empty($remote)) exit('Parameters Missing!');
            $graphPath = Tool::getRequestPath($remote);
            $result = OneDrive::getItemByPath($graphPath);
        }
        $response = OneDrive::responseToArray($result);
        if ($response['code'] === 200) {
            $can = ['html', 'htm', 'css', 'go', 'java', 'js', 'json', 'txt', 'sh', 'md', 'php', 'text', 'log'];
            if (!in_array($response['data']['ext'], $can)) exit('File Not Support');
            $download = $response['data']['@microsoft.graph.downloadUrl'] ?? exit('404 NOT FOUND');
            $content = Tool::getFileContent($download, true);
            $this->line($content);
        } else $this->warn("Failed!\n{$response['msg']} ");
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

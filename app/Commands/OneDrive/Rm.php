<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Rm extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'rm 
                            {remote : 文件地址}
                            {--id= : 可选ID}
                            {--f|force : Force Delete}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete Item';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token', ['--quiet' => true]);
        if ($this->option('force')) return $this->delete();
        if ($this->confirm('删除后仅能通过OneDrive回收站找回，确认继续吗?')) {
            return $this->delete();
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete()
    {
        $remote = $this->argument('remote');
        if ($this->option('id')) {
            $id = $this->option('id');
        } else {
            $graphPath = Tool::getRequestPath($remote);
            $id_response = Tool::handleResponse(OneDrive::pathToItemId($graphPath));
            if ($id_response['code'] === 200)
                $id = $id_response['data']['id'];
            else {
                $this->warn('路径异常!');
                exit;
            }
        }
        $response = Tool::handleResponse(OneDrive::deleteItem($id));
        $this->call('cache:clear', ['--quiet' => true]);
        $response['code'] == 200 ? $this->info("删除成功!") : $this->warn("删除失败!\n{$response['msg']} ");
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

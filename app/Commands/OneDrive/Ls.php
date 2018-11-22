<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class Ls extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'ls
                            {remote? : 远程地址}
                            {--offset=0 : 起始位置}
                            {--limit=20 : 限制数量}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List Items';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dd($this->options());
        $this->call('refresh:token', ['--quiet' => true]);
        $remote = $this->argument('remote');
        $offset = $this->option('offset');
        $length = $this->option('limit');
        $graphPath = Tool::getRequestPath($remote);
        $data = Cache::remember('one:list:' . $graphPath, Tool::config('cache_expires'), function () use ($graphPath) {
            $result = OneDrive::listChildrenByPath($graphPath);
            $response = Tool::handleResponse($result);
            return $response['code'] == 200 ? $response['data'] : [];
        });
        if (!$data) {
            $this->error('出错了，请稍后重试...');
            exit;
        }
        $data = $this->format($data);
        $items = array_slice($data, $offset, $length);
        $headers = [];
        $this->line('total ' . count($items));
        $this->table($headers, $items, 'compact');
    }

    /**
     * @param $data
     * @return array
     */
    public function format($data)
    {
        $list = [];
        foreach ($data as $item) {
            $type = array_has($item, 'folder') ? 'd' : '-';
            $size = Tool::convertSize($item['size']);
            $time = date('M m H:i', strtotime($item['lastModifiedDateTime']));
            $folder = array_has($item, 'folder') ? array_get($item, 'folder.childCount') : '1';
            $owner = array_get($item, 'createdBy.user.displayName');
            $content = [$type, $folder, $owner, $size, $time, $item['name']];
            $list[] = $content;
        }
        return $list;
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

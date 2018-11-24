<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class ListItem extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'ls
                            {remote? : Remote Path}
                            {--a|all : List All Info}
                            {--id= : ID}
                            {--offset=0 : Start}
                            {--limit=20 : Length}';

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
        $this->call('refresh:token');
        $remote = $this->argument('remote');
        $id = $this->option('id');
        $offset = $this->option('offset');
        $length = $this->option('limit');
        if ($id) {
            $data = Cache::remember('one:list:id:' . $id, Tool::config('cache_expires'), function () use ($id) {
                $result = OneDrive::getChildren($id);
                $response = Tool::handleResponse($result);
                return $response['code'] === 200 ? $response['data'] : [];
            });
        } else {
            $graphPath = Tool::getRequestPath($remote);
            $data = Cache::remember('one:list:path:' . $graphPath, Tool::config('cache_expires'), function () use ($graphPath) {
                $result = OneDrive::getChildrenByPath($graphPath);
                $response = Tool::handleResponse($result);
                return $response['code'] === 200 ? $response['data'] : [];
            });
        }
        if (!$data) {
            $this->error('Please confirm your options and try again later!');
            $this->call('cache:clear');
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
            if ($this->option('all')) {
                $content = [$type, $item['id'], $folder, $owner, $size, $time, $item['name']];
            } else {
                $content = [$type, $folder, $owner, $size, $time, $item['name']];
            }
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

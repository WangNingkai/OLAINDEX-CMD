<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Find extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'find
                            {keywords : Keywords}
                            {--id= : id}
                            {--remote=/ : Query Path}
                            {--offset=0 : Start}
                            {--limit=20 : Length}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Find Item';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        $keywords = $this->argument('keywords');
        $remote = $this->option('remote');
        $offset = $this->option('offset');
        $length = $this->option('limit');
        $graphPath = Tool::getRequestPath($remote);
        if ($id = $this->option('id')) {
            $result = OneDrive::getItem($id);
        } else {
            $result = OneDrive::search($graphPath, $keywords);
        }
        $response = OneDrive::responseToArray($result);
        $data = $response['code'] == 200 ? $response['data'] : [];
        if (!$data) {
            $this->warn('Please try again later');
            exit;
        }
        if ($id = $this->option('id')) {
            $data = [$data];
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
     * @throws \GuzzleHttp\Exception\GuzzleException
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
            if ($id = $this->option('id')) {
                $result = OneDrive::itemIdToPath($item['id']);
                $response = OneDrive::responseToArray($result);
                $path = $response['code'] == 200 ? $response['data']['path'] : 'Failed Fetch Path!';
                $content = [$type, $path, $folder, $owner, $size, $time, $item['name']];
            } else {
                $content = [$type, $item['id'], $folder, $owner, $size, $time, $item['name']];
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

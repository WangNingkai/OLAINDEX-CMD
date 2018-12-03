<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;

/**
 * Class Tool
 *
 * @package App\Helpers
 */
class Tool
{

    /**
     * Transfer File Size
     *
     * @param string $size origin
     *
     * @return string
     */
    public static function convertSize($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return @round($size, 2).$units[$i];
    }

    /**
     * Save Config
     *
     * @param $config
     *
     * @return bool
     */
    public static function saveConfig($config)
    {
        $file = storage_path('app/config.json');
        if (!is_writable($file)) {
            exit('Permission denied,Unable to write!');
        };
        $saved = file_put_contents($file, json_encode($config));

        return $saved;
    }

    /**
     * Upload Config
     *
     * @param $data
     *
     * @return bool
     */
    public static function updateConfig($data)
    {
        $config = self::config();
        $config = array_merge($config, $data);
        $saved = self::saveConfig($config);

        return $saved;
    }

    /**
     * Get Config
     *
     * @param string $key
     * @param string $default
     *
     * @return mixed|string
     */
    public static function config($key = '', $default = '')
    {
        $config = Cache::remember('config', 1440, function () {
            $file = storage_path('app/config.json');
            if (!file_exists($file)) {
                copy(storage_path('app/config.sample.json'),
                    storage_path('app/config.json'));
            };
            if (!is_readable($file)) {
                exit('Permission denied,Unable to read!');
            };
            $config = file_get_contents($file);

            return json_decode($config, true);
        });

        return $key ? (array_has($config, $key) ? (array_get($config, $key)
            ?: $default) : $default) : $config;
    }

    /**
     * Transfer Path
     *
     * @param $path
     *
     * @return mixed
     */
    public static function getAbsolutePath($path)
    {
        $path = str_replace(['/', '\\', '//'], '/', $path);

        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return str_replace('//', '/', '/'.implode('/', $absolutes).'/');
    }

    /**
     * Handle Request Path
     *
     * @param      $path
     * @param bool $isFile
     *
     * @return string
     */
    public static function getRequestPath($path, $isFile = false)
    {
        $origin_path = self::getAbsolutePath($path);
        $query_path = trim($origin_path, '/');
        $query_path = OneDrive::getEncodeUrl(rawurldecode($query_path));
        $request_path = empty($query_path) ? '/' : ":/{$query_path}:/";
        if ($isFile) {
            return rtrim($request_path, ':/');
        }

        return $request_path;
    }

    /**
     * Check Config
     *
     * @return bool
     */
    public static function hasConfig()
    {
        if (empty(self::config('client_id'))
            || empty(self::config('client_secret'))
            || empty(self::config('redirect_uri'))
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check Bind
     *
     * @return bool
     */
    public static function hasBind()
    {
        if (!empty(self::config('access_token'))
            && !empty(self::config('refresh_token'))
            && !empty(self::config('access_token_expires'))
        ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get All File
     *
     * @param $path
     *
     * @return array
     */
    public static function fetchDir($path)
    {
        $arr = [];
        $arr[] = $path;
        if (!is_file($path)) {
            if (is_dir($path)) {
                $data = scandir($path);
                if (!empty($data)) {
                    foreach ($data as $value) {
                        if ($value != '.' && $value != '..') {
                            $sub_path = $path."/".$value;
                            $temp = self::fetchDir($sub_path);
                            $arr = array_merge($temp, $arr);
                        }
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * Get Remote File Content
     *
     * @param      $url
     * @param bool $cache
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getFileContent($url, $cache = true)
    {
        if ($cache) {
            return Cache::remember('one:content:'.$url,
                self::config('cache_expires'), function () use ($url) {
                    try {
                        $client = new Client();
                        $resp = $client->request('get', $url,
                            ['stream' => true]);
                        $content = $resp->getBody()->getContents();
                        $encoding = mb_detect_encoding($content, array(
                            'GB2312',
                            'GBK',
                            'UTF-16',
                            'UCS-2',
                            'UTF-8',
                            'BIG5',
                            'ASCII',
                        ));
                        $response = mb_convert_encoding($content, 'UTF-8',
                            $encoding);
                    } catch (ClientException $e) {
                        $response = response()->json([
                            'code' => $e->getCode(),
                            'msg'  => $e->getMessage(),
                        ]);
                    }

                    return $response ?? '';
                });
        } else {
            try {
                $client = new Client();
                $resp = $client->request('get', $url, ['stream' => true]);
                $content = $resp->getBody()->getContents();
                $encoding = mb_detect_encoding($content, array(
                    'GB2312',
                    'GBK',
                    'UTF-16',
                    'UCS-2',
                    'UTF-8',
                    'BIG5',
                    'ASCII',
                ));
                $response = mb_convert_encoding($content, 'UTF-8', $encoding);
            } catch (ClientException $e) {
                $response = response()->json([
                    'code' => $e->getCode(),
                    'msg'  => $e->getMessage(),
                ]);
            }

            return $response ?? '';
        }
    }

}

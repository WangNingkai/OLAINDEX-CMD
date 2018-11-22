<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;

class Tool
{

    /**
     *文件大小转换
     * @param string $size 原始大小
     * @return string 转换大小
     */
    public static function convertSize($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return @round($size, 2) . $units[$i];
    }

    /**
     * 处理url
     * @param $path
     * @return string
     */
    public static function handleUrl($path)
    {
        $url = [];
        foreach (explode('/', $path) as $key => $value) {
            if (empty(!$value)) {
                $url[] = rawurlencode($value);
            }
        }
        return @implode('/', $url);
    }

    /**
     * 保存配置到json文件
     * @param $config
     * @return bool
     */
    public static function saveConfig($config)
    {
        $file = storage_path('app/config.json');
        if (!is_writable($file)) {
            exit('权限不足，无法写入配置文件');
        };
        $saved = file_put_contents($file, json_encode($config));
        return $saved;
    }

    /**
     * 更新配置
     * @param $data
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
     * 从json文件读取配置
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    public static function config($key = '', $default = '')
    {
        $config = Cache::remember('config', 1440, function () {
            $file = storage_path('app/config.json');
            if (!file_exists($file)) {
                copy(storage_path('app/config.sample.json'), storage_path('app/config.json'));
            };
            if (!is_readable($file)) {
                exit('权限不足，无法预取配置文件');
            };
            $config = file_get_contents($file);
            return json_decode($config, true);
        });
        return $key ? (array_has($config, $key) ? (array_get($config, $key) ?: $default) : $default) : $config;
    }

    /**
     * 解析路径
     * @param $path
     * @param bool $isFile
     * @return string
     */
    public static function getRequestPath($path, $isFile = false)
    {
        $origin_path = self::getAbsolutePath($path);
        $query_path = trim($origin_path, '/');
        $query_path = Tool::handleUrl(rawurldecode($query_path));
        $request_path = empty($query_path) ? '/' : ":/{$query_path}:/";
        if ($isFile)
            return rtrim($request_path, ':/');
        return $request_path;
    }

    /**
     * 绝对路径转换
     * @param $path
     * @return mixed
     */
    public static function getAbsolutePath($path)
    {
        $path = str_replace(['/', '\\', '//'], '/', $path);

        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return str_replace('//', '/', '/' . implode('/', $absolutes) . '/');
    }

    /**
     * 处理格式化响应
     * @param $response
     * @param bool $origin
     * @return mixed
     */
    public static function handleResponse($response, $origin = true)
    {
        if ($origin) {
            return json_decode($response, true);
        } else {
            return json_decode($response, true)['data'];
        }
    }

    /**
     * 读取文件大小
     * @param $path
     * @return bool|int|string
     */
    public static function readFileSize($path)
    {
        if (!file_exists($path))
            return false;
        $size = filesize($path);
        if (!($file = fopen($path, 'rb')))
            return false;
        if ($size >= 0) { //Check if it really is a small file (< 2 GB)
            if (fseek($file, 0, SEEK_END) === 0) { //It really is a small file
                fclose($file);
                return $size;
            }
        }
        //Quickly jump the first 2 GB with fseek. After that fseek is not working on 32 bit php (it uses int internally)
        $size = PHP_INT_MAX - 1;
        if (fseek($file, PHP_INT_MAX - 1) !== 0) {
            fclose($file);
            return false;
        }
        $length = 1024 * 1024;
        $read = '';
        while (!feof($file)) { //Read the file until end
            $read = fread($file, $length);
            $size = bcadd($size, $length);
        }
        $size = bcsub($size, $length);
        $size = bcadd($size, strlen($read));
        fclose($file);
        return $size;
    }

    /**
     * 读取文件内容
     * @param $file
     * @param $offset
     * @param $length
     * @return bool|string
     */
    public static function readFileContent($file, $offset, $length)
    {
        $handler = fopen($file, "rb") ?? die('获取文件内容失败');
        fseek($handler, $offset);
        return fread($handler, $length);
    }

    /**
     * 获取指定目录下全部子目录和文件
     * @param $path
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
                            $sub_path = $path . "/" . $value;
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
     * 判断配置
     * @return bool
     */
    public static function hasConfig()
    {
        if (empty(self::config('client_id')) || empty(self::config('client_secret')) || empty(self::config('redirect_uri'))) {
            return false;
        } else return true;
    }

    /**
     * 判断账号绑定
     * @return bool
     */
    public static function hasBind()
    {
        if (!empty(self::config('access_token')) && !empty(self::config('refresh_token')) && !empty(self::config('access_token_expires'))) {
            return true;
        } else return false;
    }

    public static function bindAccount()
    {
//        $response = OneDrive::getDrive();
    }

}

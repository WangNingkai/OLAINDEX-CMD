<?php
/**
 * This file is part of the wangningkai/olaindex-cmd.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Service;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * user defined Log class, with StreamHandler and LineFormatter
 *
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void emergency(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 */
class Log
{

    //define static log instance.
    protected static $_log_instance;

    /**
     * 获取log实例
     *
     * @return Logger
     **/
    public static function getLogInstance(): Logger
    {
        if (static::$_log_instance === null) {
            static::$_log_instance = new Logger('OALINDEX-CMD');
        }
        return static::$_log_instance;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method 可用方法: debug|info|notice|warning|error|critical|alert|emergency 可调用的方法详见 Monolog\Logger 类
     * @param array $args 调用参数
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getLogInstance();

        //组织参数信息
        $message = $args[0];
        $context = $args[1] ?? [];

        //设置日志处理手柄，默认为写入文件（还有mail、console、db、redis等方式，详见Monolog\handler 目录）
        $handler = new StreamHandler(dirname(__DIR__, 2) . '/runtime/logs/' . date('Y-m-d') . '.log');

        //设置输出格式LineFormatter(Monolog\Formatter\LineFormatter)， ignore context and extra
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));

        $instance->pushHandler($handler);

        $instance->$method($message, $context);
    }

}

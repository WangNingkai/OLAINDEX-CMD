<?php
/**
 * This file is part of the wangningkai/olaindex-cmd.
 * (c) wangningkai <i@ningkai.wang>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
if (!function_exists('is_json')) {
    /**
     * 判断字符串是否是json
     *
     * @param string $json
     * @return bool
     */
    function is_json($json)
    {
        json_decode($json, true);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}
if (!function_exists('convert_size')) {
    /**
     * 转换字节为可读取数值
     * @param int $size
     * @return string
     */
    function convert_size($size): string
    {
        $size = (int)$size;
        $units = [' B', ' KB', ' MB', ' GB', ' TB'];
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return @round($size, 2) . $units[$i];
    }
}
if (!function_exists('url_encode')) {
    /**
     * 解析路径
     *
     * @param string $path
     *
     * @return string
     */
    function url_encode($path): string
    {
        $url = [];
        foreach (explode('/', $path) as $key => $value) {
            if (empty(!$value)) {
                $url[] = rawurlencode($value);
            }
        }
        return @implode('/', $url);
    }
}
if (!function_exists('trans_request_path')) {
    /**
     * 处理请求路径
     *
     * @param string $path
     * @param bool $query
     * @param bool $isItem
     * @return string
     */
    function trans_request_path($path, $query = true, $isItem = false): string
    {
        $originPath = trans_absolute_path($path);
        $queryPath = trim($originPath, '/');
        $queryPath = url_encode(rawurldecode($queryPath));
        if (!$query) {
            return $queryPath;
        }
        $requestPath = empty($queryPath) ? '/' : ":/{$queryPath}:/";
        if ($isItem) {
            return rtrim($requestPath, ':/');
        }
        return $requestPath;
    }
}
if (!function_exists('trans_absolute_path')) {
    /**
     * 获取绝对路径
     *
     * @param string $path
     *
     * @return mixed
     */
    function trans_absolute_path($path)
    {
        $path = str_replace(['/', '\\', '//'], '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) {
                continue;
            }
            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return str_replace('//', '/', '/' . implode('/', $absolutes) . '/');
    }
}
if (!function_exists('parse_query')) {
    /**
     * Parse a query string into an associative array.
     *
     * If multiple values are found for the same key, the value of that key
     * value pair will become an array. This function does not parse nested
     * PHP style arrays into an associative array (e.g., foo[a]=1&foo[b]=2 will
     * be parsed into ['foo[a]' => '1', 'foo[b]' => '2']).
     *
     * @param string $str Query string to parse
     * @param int|bool $urlEncoding How the query string is encoded
     *
     * @return array
     */
    function parse_query($str, $urlEncoding = true)
    {
        $result = [];

        if ($str === '') {
            return $result;
        }

        if ($urlEncoding === true) {
            $decoder = function ($value) {
                return rawurldecode(str_replace('+', ' ', $value));
            };
        } elseif ($urlEncoding === PHP_QUERY_RFC3986) {
            $decoder = 'rawurldecode';
        } elseif ($urlEncoding === PHP_QUERY_RFC1738) {
            $decoder = 'urldecode';
        } else {
            $decoder = function ($str) {
                return $str;
            };
        }

        foreach (explode('&', $str) as $kvp) {
            $parts = explode('=', $kvp, 2);
            $key = $decoder($parts[0]);
            $value = isset($parts[1]) ? $decoder($parts[1]) : null;
            if (!isset($result[$key])) {
                $result[$key] = $value;
            } else {
                if (!is_array($result[$key])) {
                    $result[$key] = [$result[$key]];
                }
                $result[$key][] = $value;
            }
        }

        return $result;
    }
}
if (!function_exists('build_query')) {
    /**
     * Build a query string from an array of key value pairs.
     *
     * This function can use the return value of parse_query() to build a query
     * string. This function does not modify the provided keys when an array is
     * encountered (like http_build_query would).
     *
     * @param array $params Query string parameters.
     * @param int|false $encoding Set to false to not encode, PHP_QUERY_RFC3986
     *                            to encode using RFC3986, or PHP_QUERY_RFC1738
     *                            to encode using RFC1738.
     * @return string
     */
    function build_query(array $params, $encoding = PHP_QUERY_RFC3986)
    {
        if (!$params) {
            return '';
        }

        if ($encoding === false) {
            $encoder = function ($str) {
                return $str;
            };
        } elseif ($encoding === PHP_QUERY_RFC3986) {
            $encoder = 'rawurlencode';
        } elseif ($encoding === PHP_QUERY_RFC1738) {
            $encoder = 'urlencode';
        } else {
            throw new \InvalidArgumentException('Invalid type');
        }

        $qs = '';
        foreach ($params as $k => $v) {
            $k = $encoder($k);
            if (!is_array($v)) {
                $qs .= $k;
                if ($v !== null) {
                    $qs .= '=' . $encoder($v);
                }
                $qs .= '&';
            } else {
                foreach ($v as $vv) {
                    $qs .= $k;
                    if ($vv !== null) {
                        $qs .= '=' . $encoder($vv);
                    }
                    $qs .= '&';
                }
            }
        }

        return $qs ? (string)substr($qs, 0, -1) : '';
    }
}


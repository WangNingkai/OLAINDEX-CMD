<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

/**
 * Class OneDrive
 * @package App\Helpers
 */
class OneDrive
{
    /**
     * 请求API
     * @param $method
     * @param $param
     * @param bool $stream
     * @return false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function requestApi($method, $param, $stream = true)
    {
        if (is_array($param)) {
            @list($endpoint, $requestBody, $requestHeaders, $timeout) = $param;
            $requestBody = $requestBody ?? '';
            $headers = $requestHeaders ?? [];
            $timeout = $timeout ?? 5;
        } else {
            $endpoint = $param;
            $requestBody = '';
            $headers = [];
            $timeout = 5;
        }
        $baseUrl = Tool::config('app_type') == 'com' ? Constants::REST_ENDPOINT : Constants::REST_ENDPOINT_21V;
        $apiVersion = Constants::API_VERSION;
        if (stripos($endpoint, "http") === 0) {
            $requestUrl = $endpoint;
        } else {
            $requestUrl = $apiVersion . $endpoint;
        }
        try {
            $clientSettings = [
                'base_uri' => $baseUrl,
                'headers' => array_merge([
                    'Host' => $baseUrl,
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . Tool::config('access_token')
                ], $headers)
            ];
            $client = new Client($clientSettings);
            $response = $client->request($method, $requestUrl, [
                'body' => $requestBody,
                'stream' => $stream,
                'timeout' => $timeout,
                'allow_redirects' => [
                    'track_redirects' => true
                ]
            ]);
            return $response;
        } catch (ClientException $e) {
            return self::response('', $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 请求URL
     * @param $method
     * @param $param
     * @param bool $stream
     * @return false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function requestUrl($method, $param, $stream = true)
    {
        if (is_array($param)) {
            @list($endpoint, $requestBody, $requestHeaders, $timeout) = $param;
            $requestBody = $requestBody ?? '';
            $headers = $requestHeaders ?? [];
            $timeout = $timeout ?? 5;
        } else {
            $endpoint = $param;
            $requestBody = '';
            $headers = [];
            $timeout = 5;
        }
        try {
            $clientSettings = [
                'headers' => $headers
            ];
            $client = new Client($clientSettings);
            $response = $client->request($method, $endpoint, [
                'body' => $requestBody,
                'stream' => $stream,
                'timeout' => $timeout,
                'allow_redirects' => [
                    'track_redirects' => true
                ]
            ]);
            return $response;
        } catch (ClientException $e) {
            return self::response('', $e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取个人资料
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getMe()
    {
        $endpoint = '/me';
        $response = self::requestApi('get', $endpoint);
        return self::handleResponse($response);
    }

    /**
     * 获取盘
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getDrive()
    {
        $endpoint = '/me/drive';
        $response = self::requestApi('get', $endpoint);
        return self::handleResponse($response);
    }

    /**
     * 获取文件目录列表
     * @param $itemId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function listChildren($itemId = '')
    {
        $endpoint = $itemId ? "/me/drive/items/{$itemId}/children" : "/me/drive/root/children";
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $response = json_decode($response->getBody()->getContents(), true);
            $data = self::getNextLinkList($response);
            $res = self::formatArray($data);
            return self::response($res);
        } else {
            return $response;
        }
    }

    /**
     * 获取文件目录列表
     * @param $path
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function listChildrenByPath($path = '/')
    {
        $endpoint = $path == '/' ? "/me/drive/root/children" : "/me/drive/root{$path}children";
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $response = json_decode($response->getBody()->getContents(), true);
            $data = self::getNextLinkList($response);
            $res = self::formatArray($data);
            return self::response($res);
        } else {
            return $response;
        }
    }

    /**
     * @param $list
     * @param array $result
     * @return array|false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getNextLinkList($list, &$result = [])
    {
        if (array_has($list, '@odata.nextLink')) {
            $endpoint = str_after($list['@odata.nextLink'], Constants::REST_ENDPOINT . Constants::API_VERSION);
            $response = self::requestApi('get', $endpoint);
            $data = json_decode($response->getBody()->getContents(), true);
            $result = array_merge($list['value'], self::getNextLinkList($data, $result));
        } else {
            $result = array_merge($list['value'], $result);
        }
        return $result;
    }

    /**
     * 获取文件
     * @param $itemId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getItem($itemId)
    {
        $endpoint = "/me/drive/items/{$itemId}";
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $data = json_decode($response->getBody()->getContents(), true);
            $res = self::formatArray($data, false);
            return self::response($res);
        } else {
            return $response;
        }
    }

    /**
     * 获取文件
     * @param $path
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getItemByPath($path)
    {
        $endpoint = "/me/drive/root{$path}";
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $data = json_decode($response->getBody()->getContents(), true);
            $res = self::formatArray($data, false);
            return self::response($res);
        } else {
            return $response;
        }
    }

    /**
     * 复制文件返回进度
     * @param $itemId
     * @param $parentItemId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function copy($itemId, $parentItemId)
    {
        $drive = Tool::handleResponse(self::getDrive());
        if ($drive['code'] == 200) {
            $driveId = $drive['data']['id'];
            $endpoint = "/me/drive/items/{$itemId}/copy";
            $body = json_encode([
                'parentReference' => [
                    'driveId' => $driveId,
                    'id' => $parentItemId
                ],
            ]);
            $response = self::requestApi('post', [$endpoint, $body], false);
            if ($response instanceof Response) {
                $data = [
                    'redirect' => $response->getHeaderLine('Location')
                ];
                return self::response($data);
            } else {
                return $response;
            }
        } else {
            return self::response('', 400, '获取磁盘信息错误');
        }
    }

    /**
     * 移动文件
     * @param $itemId
     * @param $parentItemId
     * @param string $itemName
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function move($itemId, $parentItemId, $itemName = '')
    {
        $endpoint = "/me/drive/items/{$itemId}";
        $content = [
            'parentReference' => [
                'id' => $parentItemId
            ]
        ];
        if ($itemName)
            $content = array_add($content, 'name', $itemName);
        $body = json_encode($content);
        $response = self::requestApi('patch', [$endpoint, $body]);
        return self::handleResponse($response);
    }

    /**
     * 创建文件夹
     * @param $itemName
     * @param $parentItemId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function mkdir($itemName, $parentItemId)
    {
        $endpoint = "/me/drive/items/$parentItemId/children";
        $body = '{"name":"' . $itemName . '","folder":{},"@microsoft.graph.conflictBehavior":"rename"}';
        $response = self::requestApi('post', [$endpoint, $body]);
        return self::handleResponse($response);
    }

    /**
     * 创建文件夹
     * @param $itemName
     * @param $path
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function mkdirByPath($itemName, $path)
    {
        if ($path == '/')
            $endpoint = "/me/drive/root/children";
        else {
            $endpoint = "/me/drive/root{$path}children";
        }
        $body = '{"name":"' . $itemName . '","folder":{},"@microsoft.graph.conflictBehavior":"rename"}';
        $response = self::requestApi('post', [$endpoint, $body]);
        return self::handleResponse($response);
    }

    /**
     * 删除
     * @param $itemId
     * @param $eTag
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function deleteItem($itemId, $eTag = '')
    {
        $endpoint = "/me/drive/items/{$itemId}";
        $headers = $eTag ? ['if-match' => $eTag] : [];
        $response = self::requestApi('delete', [$endpoint, '', $headers]);
        if ($response instanceof Response) {
            $statusCode = $response->getStatusCode();
            if ($statusCode == 204) {
                return self::response(['deleted' => true]);
            } else {
                return self::handleResponse($response);
            }
        } else {
            return $response;
        }
    }

    /**
     * 搜索
     * @param $path
     * @param $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function search($path, $query)
    {
        if (trim($path, '/') == '')
            $endpoint = "/me/drive/root/search(q='{$query}')";
        else
            $endpoint = '/me/drive/root:/' . trim($path, '/') . ':/' . "search(q='{$query}')";
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $response = json_decode($response->getBody()->getContents(), true);
            $data = self::getNextLinkList($response);
            $res = self::formatArray($data);
            return self::response($res);
        } else {
            return $response;
        }
    }

    /**
     * 获取缩略图
     * @param $itemId
     * @param $size
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function thumbnails($itemId, $size)
    {
        $endpoint = "/me/drive/items/{$itemId}/thumbnails/0/{$size}";
        $response = self::requestApi('get', $endpoint);
        return self::handleResponse($response);
    }

    /**
     * 创建分享直链下载
     * @param $itemId
     * @return false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createShareLink($itemId)
    {
        $endpoint = "/me/drive/items/{$itemId}/createLink";
        $body = '{"type": "view","scope": "anonymous"}';
        $response = self::requestApi('post', [$endpoint, $body]);
        if ($response instanceof Response) {
            $data = json_decode($response->getBody()->getContents(), true);
            $web_url = array_get($data, 'link.webUrl');
            if (str_contains($web_url, ['sharepoint.com', 'sharepoint.cn'])) {
                $parse = parse_url($web_url);
                $domain = "{$parse['scheme']}://{$parse['host']}/";
                $param = str_after($parse['path'], 'personal/');
                $info = explode('/', $param);
                $res_id = $info[1];
                $user_info = $info[0];
                $direct_link = $domain . 'personal/' . $user_info . '/_layouts/15/download.aspx?share=' . $res_id;
            } elseif (str_contains($web_url, '1drv.ms')) {
                $client = new Client();
                try {
                    $request = $client->get($web_url, ['allow_redirects' => false]);
                    $direct_link = str_replace('redir?', 'download?', $request->getHeaderLine('Location'));
                } catch (ClientException $e) {
                    return self::response('', $e->getCode(), $e->getMessage());
                }
            } else {
                $direct_link = '';
            }
            return self::response([
                'redirect' => $direct_link
            ]);
        } else {
            return $response;
        }
    }

    /**
     * 删除分享链接
     * @param $itemId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function deleteShareLink($itemId)
    {
        $result = self::listPermission($itemId);
        $response = Tool::handleResponse($result);
        if ($response['code'] == 200) {
            $data = $response['data'];
            $permission = array_first($data, function ($value) {
                return $value['roles'][0] == 'read';
            });
            $permissionId = array_get($permission, 'id');
            return self::deletePermission($itemId, $permissionId);
        } else {
            return $result;
        }
    }

    /**
     * 列举文件权限
     * @param $itemId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function listPermission($itemId)
    {
        $endpoint = "/me/drive/items/{$itemId}/permissions";
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $data = json_decode($response->getBody()->getContents(), true);
            return self::response($data['value']);
        } else {
            return $response;
        }
    }

    /**
     * 删除指定权限
     * @param $itemId
     * @param $permissionId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function deletePermission($itemId, $permissionId)
    {
        $endpoint = "/me/drive/items/{$itemId}/permissions/{$permissionId}";
        $response = self::requestApi('delete', $endpoint);
        if ($response instanceof Response) {
            $statusCode = $response->getStatusCode();
            if ($statusCode == 204) {
                return self::response(['deleted' => true]);
            } else {
                return self::handleResponse($response);
            }
        } else {
            return $response;
        }
    }

    /**
     * 获取分享文件列表
     * @return false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getShareWithMe()
    {
        $endpoint = '/me/drive/sharedWithMe';
        $response = self::requestApi('get', $endpoint);
        return self::handleResponse($response);
    }

    /**
     * 获取分享文件详情
     * @param $driveId
     * @param $itemId
     * @return false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getShareWithMeDetail($driveId, $itemId)
    {
        $endpoint = "/drives/{$driveId}/items/{$itemId}";
        $response = self::requestApi('get', $endpoint);
        return self::handleResponse($response);
    }

    /**
     * 更新文件内容上传（4m及以下）
     * @param $id
     * @param $content
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function upload($id, $content)
    {
        $stream = \GuzzleHttp\Psr7\stream_for($content);
        $endpoint = "/me/drive/items/{$id}/content";
        $body = $stream;
        $response = self::requestApi('put', [$endpoint, $body]);
        return self::handleResponse($response);
    }

    /**
     * 上传文件（4m及以下）
     * @param $path
     * @param $content
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function uploadByPath($path, $content)
    {
        $stream = \GuzzleHttp\Psr7\stream_for($content);
        $endpoint = "/me/drive/root{$path}content";
        $body = $stream;
        $response = self::requestApi('put', [$endpoint, $body]);
        return self::handleResponse($response);
    }

    /**
     * 个人版离线下载 (实验性)
     * @param string $remote 带文件名的远程路径
     * @param string $url 链接
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function uploadUrl($remote, $url)
    {
        $drive = Tool::handleResponse(self::getDrive());
        if ($drive['code'] == 200) {
            if ($drive['data']['driveType'] == 'business') {
                return self::response(['driveType' => $drive['data']['driveType']], 400, '企业账号无法使用离线下载');
            } else {
                $path = Tool::getAbsolutePath(dirname($remote));
                // $pathId = $this->pathToItemId($path);
                // $endpoint = "/me/drive/items/{$pathId}/children"; // by id
                $handledPath = Tool::handleUrl(trim($path, '/'));
                $graphPath = empty($handledPath) ? '/' : ":/{$handledPath}:/";
                $endpoint = "/me/drive/root{$graphPath}children";
                $headers = ['Prefer' => 'respond-async'];
                $body = '{"@microsoft.graph.sourceUrl":"' . $url . '","name":"' . pathinfo($remote, PATHINFO_BASENAME) . '","file":{}}';
                $response = self::requestApi('post', [$endpoint, $body, $headers]);
                if ($response instanceof Response) {
                    $data = [
                        'redirect' => $response->getHeaderLine('Location')
                    ];
                    return self::response($data);
                } else {
                    return $response;
                }
            }
        } else {
            return $drive;
        }
    }

    /**
     * 大文件上传创建session
     * @param $remote
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createUploadSession($remote)
    {
        $endpoint = "/me/drive/root{$remote}createUploadSession";
        $body = json_encode([
            'item' => [
                '@microsoft.graph.conflictBehavior' => 'fail',
            ]
        ]);
        $response = self::requestApi('post', [$endpoint, $body]);
        return self::handleResponse($response);

    }

    /**
     * 分片上传
     * @param $url
     * @param $file
     * @param $offset
     * @param int $length
     * @return false|mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function uploadToSession($url, $file, $offset, $length = 5242880)
    {
        $file_size = Tool::readFileSize($file);
        $content_length = (($offset + $length) > $file_size) ? ($file_size - $offset) : $length;
        $end = (($offset + $length) > $file_size) ? ($file_size - 1) : $offset + $content_length - 1;
        $content = Tool::readFileContent($file, $offset, $length);
        $headers = [
            'Content-Length' => $content_length,
            'Content-Range' => "bytes {$offset}-{$end}/{$file_size}",
        ];
        $requestBody = $content;
        $response = self::requestUrl('put', [$url, $requestBody, $headers, 360]);
        return self::handleResponse($response);
    }

    /**
     * 分片上传状态
     * @param $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function uploadSessionStatus($url)
    {
        $response = self::requestUrl('get', $url);
        return self::handleResponse($response);
    }

    /**
     * 删除分片上传任务
     * @param $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function deleteUploadSession($url)
    {
        $response = self::requestUrl('delete', $url);
        return self::handleResponse($response);
    }

    /**
     * id转path
     * @param $itemId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function itemIdToPath($itemId)
    {
        $result = self::getItem($itemId);
        $response = Tool::handleResponse($result);
        if ($response['code'] == 200) {
            $item = $response['data'];
            if (!array_key_exists('path', $item['parentReference']) && $item['name'] == 'root') {
                return self::response([
                    'path' => '/'
                ]);
            }
            $path = $item['parentReference']['path'];
            if (starts_with($path, '/drive/root:')) {
                $path = str_after($path, '/drive/root:');
            }
            // 兼容根目录
            if ($path == '') {
                $pathArr = [];
            } else {
                $pathArr = explode('/', $path);
                if (trim(Tool::config('root'), '/') != '') {
                    $pathArr = array_slice($pathArr, 1);
                }
            }
            array_push($pathArr, $item['name']);
            $path = trim(implode('/', $pathArr), '/');
            return self::response([
                'path' => $path
            ]);
        } else {
            return $result;
        }
    }

    /**
     * path转id
     * @param $path
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function pathToItemId($path)
    {
        $endpoint = $path == '/' ? '/me/drive/root' : '/me/drive/root' . $path;
        $response = self::requestApi('get', $endpoint);
        if ($response instanceof Response) {
            $response = json_decode($response->getBody()->getContents(), true);
            return self::response(['id' => $response['id']]);
        } else {
            return $response;
        }
    }

    /**
     * 处理响应
     * @param $response Response
     * @return false|string
     */
    public static function handleResponse($response)
    {
        if (in_array($response->getStatusCode(), [200, 201, 202, 204])) {
            $data = json_decode($response->getBody()->getContents(), true);
            return self::response($data);
        } else {
            return $response;
        }
    }

    /**
     * 文件信息格式化
     * @param $response
     * @param bool $isList
     * @return array
     */
    public static function formatArray($response, $isList = true)
    {
        if ($isList) {
            $items = [];
            foreach ($response as $item) {
                if (array_has($item, 'file')) $item['ext'] = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION));
                $items[$item['name']] = $item;
            }
            return $items;
        } else {
            // 兼容文件信息
            $response['ext'] = strtolower(pathinfo($response['name'], PATHINFO_EXTENSION));
            return $response;
        }
    }

    /**
     * @param $data
     * @param int $code
     * @param string $msg
     * @return false|string
     */
    public static function response($data, $code = 200, $msg = 'ok')
    {
        return json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }
}

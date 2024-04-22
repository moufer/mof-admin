<?php

namespace module\miniapp\library\wechat;

use EasyWeChat\Kernel\HttpClient\Response;
use mof\exception\LogicException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class AppCode extends MiniAppBase
{
    /**
     * 临时小程序码
     * @param string $path
     * @param string $width
     * @param array $option
     * @return ResponseInterface|Response
     */
    public function limit(string $path, string $width = '430', array $option = []): ResponseInterface|Response
    {
        $data = [
            'path'  => $path,
            'width' => intval($width),
        ];
        if (!empty($option['auto_color'])) {
            $data['auto_color'] = intval($option['auto_color']);
        }
        if (!empty($option['line_color'])) {
            $data['line_color'] = [
                'r' => $option['line_color']['r'],
                'g' => $option['line_color']['g'],
                'b' => $option['line_color']['b'],
            ];
            $data['auto_color'] = false;
        }
        try {
            return $this->app->getClient()->postJson('/wxa/getwxacode', $data);
        } catch (TransportExceptionInterface $e) {
            throw new LogicException($e->getMessage());
        }
    }

    /**
     * 永久小程序码
     * @param string $scene
     * @param string $page
     * @param string $width
     * @param array $option
     * @return ResponseInterface|Response
     */
    public function unlimit(string $scene, string $page = '', string $width = '430', array $option = []): ResponseInterface|Response
    {
        $data = [
            'scene' => $scene,
            'width' => intval($width),
        ];
        if (!empty($page)) {
            $data['page'] = $page;
        }
        if (!empty($option['auto_color'])) {
            $data['auto_color'] = intval($option['auto_color']);
        }

        if (!empty($option['line_color'])) {
            $data['line_color'] = [
                'r' => $option['line_color']['r'],
                'g' => $option['line_color']['g'],
                'b' => $option['line_color']['b'],
            ];
            $data['auto_color'] = false;
        }

        try {
            return $this->app->getClient()->postJson('/wxa/getwxacodeunlimit', $data);
        } catch (TransportExceptionInterface $e) {
            throw new LogicException($e->getMessage());
        }
    }

}
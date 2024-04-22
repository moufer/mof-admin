<?php

namespace module\miniapp\controller\frontend\common;

use GuzzleHttp\Client;
use module\miniapp\library\MiniappFrontendController;
use mof\ApiController;
use mof\ApiResponse;
use mof\Request;
use think\Response;
use think\response\Json;

class Utils extends ApiController
{
    /**
     * 图片中转
     */
    public function transferImg(Request $request): Json|Response
    {
        $image = $request->get('attach');
        if (!$image) return ApiResponse::error("未指定图片");
        $headers = [];
        if (strpos($image, '.qpic.cn') > 0) {
            //设置REFERER
            $headers['Referer'] = 'https://v.qq.com';
        }
        $headers['User-Agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
            . '(KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36';
        $client = new Client([
            'headers' => $headers,
        ]);
        $res = $client->get($image);
        $code = $res->getStatusCode();
        $base64 = $request->get('base64/d');

        if ($code === 200) {
            if ($base64) {
                $base64Image = "data:image/jpeg;base64," . base64_encode($res->getBody()->getContents());
                return ApiResponse::success($base64Image);
            } else {
                return response(
                    $res->getBody()->getContents(),
                    200,
                    ['Content-Length' => $res->getBody()->getSize()]
                )->contentType('image/jpeg');
            }
        } else {
            return ApiResponse::error("图片不存在({$code})");
        }
    }
}
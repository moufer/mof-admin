<?php

namespace app\concern;

use app\model\Storage;
use mof\ApiController;
use mof\ApiResponse;
use think\exception\ValidateException;
use think\Image;
use think\response\Json;

/**
 * 文件上传
 * @package app\concern
 * @mixin ApiController
 */
trait Upload
{
    public function file(): Json
    {
        return $this->upload('file');
    }

    public function media(): Json
    {
        return $this->upload('media');
    }

    public function image(): Json
    {
        $rules = [];
        $messages = [];
        $width = $this->request->param('width', 0);
        $height = $this->request->param('height', 0);
        if ($width > 0 && $height > 0) {
            $rules[] = 'image:' . $width . ',' . $height;
            $messages['file.image'] = '上传的图片尺寸必须是' . $width . 'x' . $height;
        } else {
            $rules[] = 'image';
            $messages['file.image'] = '上传的文件不是一个有效的图片文件';
        }
        return $this->upload('image', $rules, $messages);
    }

    protected function upload($type, $rules = [], $messages = []): Json
    {
        //加入尺寸和类型限制
        foreach (['image', 'media', 'file'] as $key) {
            if ($type !== $key) continue;
            $ext = config('admin.storage_' . $key . '_ext');
            $size = (int)config('admin.storage_' . $key . '_size', 0);
            $ext && $rules[] = 'fileExt:' . $ext;                  //文件后缀验证
            $size && $rules[] = 'fileSize:' . $size * 1024 * 1024; //文件data大小验证
        }
        //验证规则
        $rules = array_merge(['require', 'file'], $rules);
        //验证消息
        $messages = array_merge([
            'file.require'  => '请上传文件',
            'file.file'     => '请上传文件',
            'file.fileExt'  => '不支持上传的文件类型',
            'file.fileSize' => '文件大小超过了最大限制',
        ], $messages);

        //验证
        try {
            $file = $this->request->file('file');
            $this->request->withValidate(['file' => $rules], $messages)->validate(['file' => $file]);
        } catch (ValidateException $exception) {
            return ApiResponse::fail($exception->getMessage());
        }

        //如果是图片并且限制了最大宽高，则缩小图片到最大宽高
        if ($type == 'image') {
            //获取图片宽高
            $wh = config('admin.storage_image_wh', '');
            if (preg_match('/^(\d+)x(\d+)$/', $wh, $match)) {
                $maxW = (int)$match[1];
                $maxH = (int)$match[2];
                if ($maxH && $maxW) {
                    $image = Image::open($file);
                    $image->thumb($maxW, $maxH)
                        ->save($file->getRealPath());
                }
            }
        }

        //保存目录
        $dirs = ['image' => 'images', 'media' => 'media'];
        $dir = $this->request->param('dir', $dirs[$type] ?? 'files');

        $local = $this->request->param('local/d', 0); //是否存在本地
        $fs = $this->app->filesystem->disk($local ? 'local' : null);

        $path = $fs->putFile($dir, $file);    //保存文件
        if (!$path) {
            return ApiResponse::error('文件保存失败');
        }

        //获取文件信息
        //$filePath = $fs->path($path);
        //$url = $fs->url($saveName);

        //保存到数据库
        $data = [
            'user_type' => $this->auth->getUser()->getUserType(),
            'user_id'   => $this->auth->getId() ?? 0,
            'name'      => basename($path),
            'title'     => $file->getOriginalName(),
            'path'      => $path,
            'size'      => $file->getSize(),
            'mime'      => $file->getOriginalMime(),
            'sha1'      => $file->hash('sha1'),
            'provider'  => $this->app->filesystem->getDefaultDriver(),
        ];
        if ('image' == $type && isset($image)) {
            $data['width'] = $image->width();
            $data['height'] = $image->height();
        }

        if (!$local) {
            //保存到数据库
            $storage = Storage::create($data);

            //触发事件
            event('StorageUpload', $storage);

            //返回
            $data = $storage->visible([
                'id', 'title', 'url', 'path', 'size', 'mime', 'width', 'height'
            ])->toArray();
        } else {
            $data['url'] = $path;
        }

        //获取用户附加参数，并原样返回
        $data['extra'] = $this->request->param('extra', '');

        return ApiResponse::success($data);
    }
}
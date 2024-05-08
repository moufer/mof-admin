<?php
// 应用公共文件
use mof\Mof;

/**
 * 获取上传地址
 * @param string $method 上传方法
 * @return string 上传地址
 */
function upload_url(string $method = 'image'): string
{
    return url('/system/upload/' . $method)->domain(true)->build();
}

/**
 * 附件链接
 * @param string $path
 * @return string
 */
function storage_url(string $path): string
{
    return Mof::storageUrl($path);
}
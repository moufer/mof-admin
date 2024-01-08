<?php
// 应用公共文件

/**
 * 获取上传地址
 * @param string $method 上传方法
 * @return string 上传地址
 */
function upload_url(string $method = 'image'): string
{
    return url('/admin/upload/' . $method)->domain(true)->build();
}
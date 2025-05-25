<?php

namespace app\controller;

use app\library\Controller;

class Upload extends Controller
{
    use \app\concern\Upload;

    /** @var string $prefix 前缀文件夹，区别不同模块上传的资源 */
    protected string $prefixDirectory = 'system';

    /** @var array $extend 保存的扩展信息，用于指定是那个模块上传的 */
    protected array $extend = [
        'type' => 'system',
        'id'   => 0,
    ];
}

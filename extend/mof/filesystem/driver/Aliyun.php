<?php

namespace mof\filesystem\driver;

use League\Flysystem\AdapterInterface;
use mof\filesystem\concern\Storage;
use think\filesystem\Driver;
use Xxtime\Flysystem\Aliyun\OssAdapter;

class Aliyun extends Driver
{
    use Storage;

    protected function createAdapter(): AdapterInterface
    {
        return new OssAdapter([
            'accessId'     => $this->config['accessId'],
            'accessSecret' => $this->config['accessSecret'],
            'bucket'       => $this->config['bucket'],
            'endpoint'     => $this->config['endpoint'],
        ]);
    }
}
<?php

/**
 * Author: moufer <moufer@163.com>
 * Date: 2024/12/20 09:58
 */

namespace mof\filesystem\driver;

use mof\filesystem\concern\Storage;
use think\filesystem\Driver;
use League\Flysystem\AdapterInterface;

class Ftp extends Driver
{
    use Storage;

    protected function createAdapter(): AdapterInterface
    {
        $config = [
            'host'                 => $this->config['host'],
            'username'             => $this->config['username'],
            'password'             => $this->config['password'],
            'port'                 => $this->config['port'] ?? 21,
            'root'                 => $this->config['port'],
            'passive'              => $this->config['passive'] ?? false,
            'ssl'                  => $this->config['ssl'] ?? false,
            'timeout'              => $this->config['timeout'] ?? 5,
            'ignorePassiveAddress' => $this->config['timeout'] ?? false,
        ];

        return new \League\Flysystem\Adapter\Ftp($config);
    }
}

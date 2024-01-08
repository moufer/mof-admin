<?php

namespace mof\filesystem\concern;

use RuntimeException;
use think\filesystem\Driver;

/**
 * @mixin Driver
 */
trait Storage
{
    /**
     * 获取文件访问地址
     * @param string $path 文件路径
     * @return string
     */
    public function url(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);

        if (isset($this->config['url'])) {
            return $this->concatPathToUrl($this->config['url'], $path);
        } else {
            $adapter = $this->filesystem->getAdapter();
            if (method_exists($adapter, 'getUrl')) {
                return $adapter->getUrl($path);
            }
        }
        throw new RuntimeException('This driver does not support retrieving URLs.');
    }
}
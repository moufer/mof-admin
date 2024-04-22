<?php

namespace mof\utils;

use mof\exception\LogicException;

class Dir
{
    /**
     * 获取指定目录及其子目录下的所有文件
     * @param string $dir 目录路径
     * @return array 文件列表
     */
    public static function getFiles(string $dir, string $ext = '*'): array
    {
        $files = [];
        if (is_dir($dir)) {
            $dirHandle = opendir($dir);
            while ($file = readdir($dirHandle)) {
                if ($file != '.' && $file != '..') {
                    $filePath = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($filePath)) {
                        $files = array_merge($files, self::getFiles($filePath, $ext));
                    } else if ($ext == '*' || strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) == $ext) {
                        $files[] = $filePath;
                    }
                }
            }
            closedir($dirHandle);
        }
        return $files;
    }

    /**
     * 复制文件(自动创建目标目录)
     * @param string $sourceFilePath 源文件
     * @param string $destFilePath 目标文件
     * @return bool 是否复制成功
     */
    public static function copyFile(string $sourceFilePath, string $destFilePath): bool
    {
        // 创建目标目录(如果不存在)
        $destDir = dirname($destFilePath);
        if (!is_dir($destDir)) {
            if (!mkdir($destDir, 0755, true)) {
                throw new LogicException("文件夹({$destDir})创建失败");
            }
        }

        // 复制文件
        return copy($sourceFilePath, $destFilePath);
    }

    /**
     * 复制整个文件夹
     * @param string $source 源文件夹
     * @param string $dest 目标目录
     * @return int 复制文件的数量
     */
    public static function copyDir(string $source, string $dest): int
    {
        $num = 0;
        $files = self::getFiles($source);
        foreach ($files as $file) {
            //目标文件路径
            $destFile = str_replace(
                rtrim($source, DIRECTORY_SEPARATOR),
                rtrim($dest, DIRECTORY_SEPARATOR),
                $file
            );
            self::copyFile($file, $destFile) && $num++;
        }
        return $num;
    }

    /**
     * 删除与第一个目录重名的文件
     * @param string $sourceFileDir 第一个对比目录
     * @param string $removeFileDir 第二个对比目录（准备删除文件的目录）
     * @return int 删除文件的数量
     */
    public static function removeRedundantFiles(string $sourceFileDir, string $removeFileDir): int
    {
        $files1 = self::getFiles($sourceFileDir);
        $files2 = self::getFiles($removeFileDir);

        // 构建第一个目录的文件名到路径的映射
        $fileMap = [];
        foreach ($files1 as $file) {
            $fileMap[basename($file)] = $file;
        }

        // 删除第二个目录中与第一个目录重名的文件
        $num = 0;
        foreach ($files2 as $file) {
            $fileName = basename($file);
            if (array_key_exists($fileName, $fileMap)) {
                unlink($file) && $num++;
            }
        }

        return $num;
    }

    /**
     * 递归删除目录中的空子目录
     *
     * @param string $dir 目录路径
     * @return bool 操作是否成功
     */
    public static function removeEmptySubdirs(string $dir, bool $removeRoot = true, int $deep = 0): bool
    {
        $success = true;
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                if (!self::removeEmptySubdirs($path, false, $deep + 1)) {
                    $success = false;
                } elseif (count(scandir($path)) === 2) {
                    if (!rmdir($path)) {
                        $success = false;
                    }
                }
            }
        }

        //删除第一个目录
        if ($deep === 0 && $removeRoot && count(scandir($dir)) === 2) {
            if (!rmdir($dir)) {
                $success = false;
            }
        }

        return $success;
    }
}
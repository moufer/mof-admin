<?php

namespace module\miniapp\library;

use http\Url;
use module\miniapp\model\MiniApp;
use mof\exception\LogicException;
use mof\Module;
use ZipArchive;

/**
 * 小程序打包
 */
class WechatMiniAppPackage
{
    protected MiniApp $miniapp;

    protected array $appJson           = [];
    protected array $configJs          = [];
    protected array $projectConfigJson = [];

    /** @var string 小程序包路径 */
    protected string $path = '';

    /**
     * @param MiniApp $miniapp
     * @throws LogicException
     */
    public function __construct(MiniApp $miniapp)
    {
        $this->miniapp = $miniapp;

        $this->path = Module::path($miniapp->module) . '/miniapp/wechat/';
        $this->path = str_replace('/', DIRECTORY_SEPARATOR, $this->path);

        if (!$this->checkIntegrity()) {
            throw new LogicException('小程序包不存在或已损坏');
        }

        $this->loadConfigJsFile();
        $this->loadAppJsonFile();
        $this->loadProjectConfigJsonFile();
    }

    /**
     * 检测小程序包完整性
     * @return bool
     */
    public function checkIntegrity(): bool
    {
        if (!is_dir($this->path)) {
            return false;
        }
        $checkFiles = [
            '/app.json', '/app.js', '/project.config.json',
        ];
        foreach ($checkFiles as $file) {
            if (!file_exists($this->path . $file)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取小程序内引用的插件
     * @param bool $optionFormat
     * @return array
     */
    public function getPlugins(bool $optionFormat = false): array
    {
        $plugins = $this->appJson['plugins'] ?? [];
        if (!empty($this->appJson['subPackages'])) {
            foreach ($this->appJson['subPackages'] as $subPackages) {
                if (!empty($subPackages['plugins'])) {
                    $plugins = array_merge($plugins, $subPackages['plugins']);
                }
            }
        }
        if ($optionFormat && $plugins) {
            $options = [];
            foreach ($plugins as $key => $val) {
                $options[] = [
                    'caption' => "【{$val['provider']}】{$key}",
                    'label'   => $val['provider'],
                ];
            }
            return $options;
        }
        return $plugins;
    }

    /**
     * 设置小程序引用的插件
     * @param $plugins array 引用的插件数组
     * @return void
     */
    public function setPlugins(array $plugins): void
    {
        if (!empty($this->appJson['plugins'])) {
            //遍历 $this->appJson['plugins'] 判断内容是否在$plugins里也存在
            foreach ($this->appJson['plugins'] as $key => $val) {
                if (!in_array($val['provider'], $plugins)) {
                    unset($this->appJson['plugins'][$key]);
                }
            }
        }
        if ($this->appJson['subPackages']) {
            //遍历 $this->appJson['subPackages'] 判断内容是否在$plugins里也存在
            foreach ($this->appJson['subPackages'] as $key => $subPackages) {
                foreach ($subPackages['plugins'] as $_key => $val) {
                    if (!in_array($val['provider'], $plugins)) {
                        unset($this->appJson['subPackages'][$key]['plugins'][$_key]);
                    }
                }
//                if(empty($this->appJson['subPackages'][$key]['plugins'])) {
//                    unset($this->appJson['subPackages'][$key]);
//                }
            }
        }
    }

    /**
     * 设置小程序配置
     * @param $data
     * @return void
     */
    public function setValues($data): void
    {
        foreach (['siteroot', 'plugins'] as $key) {
            if (isset($data[$key])) {
                $this->configJs[$key] = $data[$key];
            }
        }
        $this->setPlugins($data['plugins']);
    }

    /**
     * zip打包
     * @param string $name
     * @return string
     */
    public function zip(string $name): string
    {
        if (!class_exists('ZipArchive')) {
            throw new LogicException('未安装php-zip扩展');
        }
        //zip文件
        $zipName = $name . '.zip';
        $zipFile = app()->getRuntimePath() . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            //忽略打包的文件
            $excludeFiles = array_map(
                fn($name) => str_replace('/', DIRECTORY_SEPARATOR, $name),
                [
                    '/siteinfo.js',
                    '/app.json',
                    '/project.config.json',
                ]
            );
            // 递归添加文件和子文件夹到压缩文件
            $this->addFolderToZip($this->path, $zip, '', $excludeFiles);
            //加入自定义的app.json, siteinfo.js文件
            $zip->addFromString('siteinfo.js', $this->generateConfigJs());
            $zip->addFromString('app.json', $this->generateAppJson());
            $zip->addFromString('project.config.json', $this->generateProjectConfigJson());
            $zip->close();
            return $zipFile;
        } else {
            throw new LogicException('小程序打包失败');
        }
    }

    protected function generateConfigJs(): string
    {
        $string = json_encode($this->configJs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return 'module.exports = ' . $string . ';';
    }

    protected function generateAppJson(): string
    {
        return json_encode($this->appJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    protected function generateProjectConfigJson(): string
    {
        return json_encode($this->projectConfigJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * 载入app.json
     * @return void
     */
    protected function loadAppJsonFile(): void
    {
        $content = $this->getFileContent('app.json');
        if (!$json = json_decode($content, true)) {
            throw new LogicException('小程序配置文件为空');
        } else if (!is_array($json)) {
            throw new LogicException('小程序配置文件无效');
        } else {
            if (!isset($json['pages'])) {
                throw new LogicException('小程序配置文件不完整');
            }
        }
        $this->appJson = $json;
    }

    /**
     * 载入siteinfo.js
     * @return void
     */
    protected function loadConfigJsFile(): void
    {
        $domain = rtrim(url()->domain(true)->build(), '/');
        $data = [];
        try {
            $content = str_replace(' ', '', $this->getFileContent('siteinfo.js'));
            $cleanedCode = str_replace('module.exports=', '', $content);
            //过滤换行
            $cleanedCode = str_replace("\r\n", '', $cleanedCode);
            $data = json_decode($cleanedCode, true);
        } catch (\Exception) {
        }
        $data['id'] = $this->miniapp->id;
        $data['module'] = $this->miniapp->module;
        $data['siteroot'] = $domain;
        $data['serverUrl'] = $domain . "/{$this->miniapp->module}/{$this->miniapp->id}/wechat";
        $data['staticUrl'] = $domain . "/static/module/{$this->miniapp->module}";
        $this->configJs = $data;
    }

    /**
     * @return void
     */
    protected function loadProjectConfigJsonFile(): void
    {
        $content = $this->getFileContent('project.config.json');
        if (!$json = json_decode($content, true)) {
            throw new LogicException('小程序项目配置文件为空');
        } else if (!is_array($json)) {
            throw new LogicException('小程序项目配置文件无效');
        } else {
            if (!isset($json['setting'])) {
                throw new LogicException('小程序配置文件不完整');
            }
        }
        $json['appid'] = $this->miniapp->appid;
        $json['projectName'] = $this->miniapp->module;
        $this->projectConfigJson = $json;
    }

    /**
     * 载入文件内容
     * @param $filename
     * @return string
     */
    protected function getFileContent($filename): string
    {
        $ds = DIRECTORY_SEPARATOR;
        $file = rtrim($this->path, $ds) . $ds . $filename;
        if (!file_exists($file)) {
            throw new LogicException('小程序配置文件不存在');
        } else if (!$content = file_get_contents($file)) {
            throw new LogicException('小程序配置文件不完整');
        }
        return $content;
    }

    /**
     * 压缩包加入文件夹
     * @param string $folderPath
     * @param ZipArchive $zip
     * @param string $parentFolder
     * @param array $excludeFiles
     * @return void
     */
    protected function addFolderToZip(string $folderPath, ZipArchive $zip, string $parentFolder = '',
                                      array  $excludeFiles = []): void
    {
        $handle = opendir($folderPath);
        while (($file = readdir($handle)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
                //准备加入的文件或文件夹
                $entryName = $parentFolder . DIRECTORY_SEPARATOR . $file;
                //排除忽略的
                if (in_array($entryName, $excludeFiles)) {
                    continue;
                }
                if (is_file($filePath)) {
                    $zip->addFile($filePath, $entryName);
                } elseif (is_dir($filePath)) {
                    // 创建子文件夹
                    $zip->addEmptyDir($entryName);
                    // 递归添加
                    $this->addFolderToZip($filePath, $zip, $entryName, $excludeFiles);
                }
            }
        }
        closedir($handle);
    }

}
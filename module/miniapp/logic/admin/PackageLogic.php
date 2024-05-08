<?php

namespace module\miniapp\logic\admin;

use app\library\Auth;
use http\Url;
use module\miniapp\library\WechatMiniAppPackage;
use module\miniapp\model\MiniApp;
use module\miniapp\model\Package;
use mof\annotation\Inject;
use mof\exception\LogicException;
use mof\Logic;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\response\File;

class PackageLogic extends Logic
{
    #[Inject]
    protected MiniApp $miniapp;

    #[Inject]
    protected Auth $auth;

    /**
     * 小程序打包配置表单
     * @return array[]
     * @throws LogicException
     */
    public function form(): array
    {
        //设置默认数值
        $values['siteroot'] = rtrim(url()->domain(true)->build(), '/');
        $values['plugins'] = [];
        return $this->getFormConfig($values);
    }

    /**
     * 提交打包
     * @throws \Exception
     */
    public function submit(array $postData): array
    {
        $package = new WechatMiniAppPackage($this->miniapp);
        $package->setValues($postData); //设置打包配置

        //定义文件名(不含后缀)
        $name = sprintf('%s%s',
            md5($this->miniapp->module . $this->miniapp->id . $this->auth->getId()),
            '.miniapp'
        );
        $filePath = $package->zip($name);
        //判断文件是否存在
        if (!file_exists($filePath)) {
            throw new LogicException('打包失败，请重试');
        }

        //下载key,通过这个key来获取对应的打包文件
        $key = md5(uniqid());
        //记录写入数据库
        try {
            Package::create([
                'key'        => $key,
                'miniapp_id' => $this->miniapp->id,
                'admin_id'   => $this->auth->getId(),
                'module'     => $this->miniapp->module,
                'filename'   => basename($filePath),
                'filesize'   => filesize($filePath),
                'package_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            //删除已打包的文件
            if (!empty($filePath) && file_exists($filePath)
                && pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
                unlink($filePath);
            }
            throw $e;
        }

        //返回打包下载信息
        return [
            'key'      => $key,
            'filename' => "{$this->miniapp->module}_wxapp.zip"
        ];
    }

    /**
     * 下载
     * @param string $key
     * @return File
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function download(string $key): File
    {
        //查找打包记录
        $packageModel = Package::where([
            'key'        => $key,
            'miniapp_id' => $this->miniapp->id,
            'admin_id'   => $this->auth->getId(),
        ])->find();

        if (!$packageModel) {
            throw new LogicException('打包信息不存在');
        }

        //获取打包文件
        $filePath = app()->getRuntimePath() . $packageModel->filename;
        if (!file_exists($filePath)) {
            throw new LogicException('打包文件不存在');
        }

        //验证是否过期，300秒为过期时间
        if (time() - strtotime($packageModel->package_at) > 300) {
            //删除已过期文件
            unlink($filePath);
            throw new LogicException('下载已过期');
        }

        if (!function_exists('finfo_open')) {
            throw new LogicException('PHP未安装 fileinfo 扩展');
        }

        //已下载更新
        $packageModel->save([
            'downloaded'  => 1,
            'download_at' => date('Y-m-d H:i:s')
        ]);

        try {
            //下载
            $name = $this->miniapp->module . '_wxapp.zip';
            return download($filePath, $name);
        } catch (\Exception $e) {
            throw new LogicException('下载失败');
        }
    }

    /**
     * 完成下载
     * @param string $key
     * @return void
     * @throws DbException
     */
    public function downloaded(string $key): void
    {
        $package = Package::where([
            'key'        => $key,
            'miniapp_id' => $this->miniapp->id,
            'admin_id'   => $this->auth->getId(),
        ])->find();

        if ($package) {
            //删除文件
            $filePath = app()->getRuntimePath() . $package->filename;
            if (!empty($filePath) && file_exists($filePath)
                && pathinfo($filePath, PATHINFO_EXTENSION) === 'zip') {
                unlink($filePath);
            }
        }
    }

    /**
     * 表单选项
     * @param array $values
     * @return array[]
     * @throws LogicException
     */
    private function getFormConfig(array $values): array
    {
        $result = [];
        //源代码包
        $package = new WechatMiniAppPackage($this->miniapp);
        $result[] = [
            "label" => "请求网址",
            "prop"  => "siteroot",
            "type"  => "input",
            "value" => $values['siteroot'] ?? 'https://',
            "rules" => [
                ["required" => true],
                ["type" => "url"],
                ["pattern" => "^https:\/\/", "message" => "必须以https://开头"],
            ],
            "intro" => "请填写小程序通信接口地址，必须是https。如无特殊情况，请保持默认值。"
        ];
        if ($plugins = $package->getPlugins(true)) {
            $result[] = [
                "label"   => "启用插件",
                "prop"    => "plugins",
                "type"    => "checkbox",
                "intro"   => "选择要启用的插件，启用前请先到微信小程序后台添加小程序插件。",
                "value"   => $values['plugins'] ?? '',
                "options" => $plugins
            ];
        }
        return $result;
    }
}
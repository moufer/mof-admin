<?php

namespace module\miniapp\logic;

use module\miniapp\library\WechatMiniAppPackage;
use module\miniapp\model\Package;
use mof\exception\LogicException;
use mof\Logic;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\response\File;

class PackageLogic extends Logic
{
    /**
     * 小程序打包配置表单
     * @return array[]
     * @throws LogicException
     */
    public function form(): array
    {
        //设置默认数值
        $values['app_name'] = $this->request->miniapp->title;
        $values['app_url'] = $this->request->miniapp->api_root;
        $values['plugins'] = [];
        return $this->getFormConfig($values);
    }

    /**
     * 提交打包
     * @throws \Exception
     */
    public function submit(array $postData): array
    {
        $miniapp = $this->request->miniapp;

        $package = new WechatMiniAppPackage($this->request->miniapp);
        $package->setValues($postData); //设置打包配置

        //定义文件名(不含后缀)
        $name = sprintf('%s%s',
            md5($miniapp->module . $miniapp->id . $this->request->user->id),
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
            $model = new Package();
            $model->save([
                'key'      => $key,
                'ma_id'    => $miniapp->id,
                'admin_id' => $this->request->user->id,
                'module'   => $miniapp->module,
                'filename' => basename($filePath),
                'filesize' => filesize($filePath),
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
            'filename' => "{$miniapp->module}_wxapp.zip"
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
            'key'      => $key,
            'ma_id'    => $this->request->miniapp->id,
            'admin_id' => $this->request->user->id,
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

        //已下载更新
        $packageModel->save([
            'downloaded'  => 1,
            'download_at' => date('Y-m-d H:i:s')
        ]);

        //下载
        $name = $this->request->miniapp->module . '_wxapp.zip';
        return download($filePath, $name);
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
            'key'      => $key,
            'ma_id'    => $this->request->miniapp->id,
            'admin_id' => $this->request->user->id,
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
        //源代码包
        $package = new WechatMiniAppPackage($this->request->miniapp);
        return [
            [
                "label" => "小程序名称",
                "prop"  => "app_name",
                "type"  => "input",
                "value" => $values['app_name'] ?? '',
                "rules" => [
                    ["required" => true],
                ],
                "intro" => "请填写小程序名称。"
            ],
            [
                "label" => "请求网址",
                "prop"  => "app_url",
                "type"  => "input",
                "value" => $values['app_url'] ?? 'https://',
                "rules" => [
                    ["required" => true],
                    ["type" => "url"],
                    ["pattern" => "^https:\/\/", "message" => "必须以https://开头"],
                ],
                "intro" => "请填写小程序通信接口地址，必须是https。如无特殊情况，请保持默认值。"
            ],
            [
                "label"   => "启用插件",
                "prop"    => "plugins",
                "type"    => "checkbox",
                "intro"   => "选择要启用的插件，启用前请先到微信小程序后台添加小程序插件。",
                "value"   => $values['plugins'] ?? '',
                "options" => $package->getPlugins(true)
            ]
        ];
    }
}
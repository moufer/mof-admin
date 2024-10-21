<?php

namespace app\library;

use app\library\perm\{PermAction, PermGroup, PermMenu};
use app\model\Perm;
use mof\exception\LogicException;
use mof\Module;
use mof\utils\AnnotationParser;
use think\db\exception\{DataNotFoundException, DbException, ModelNotFoundException};
use think\db\Raw;
use think\facade\Db;
use think\helper\Str;

/**
 * 安装权限菜单
 */
class InstallPerm
{
    /**
     * 模块名称
     * @var string
     */
    protected string $moduleName;

    public static function make($moduleName): static
    {
        $moduleInfo = \mof\Module::info($moduleName);
        if (!$moduleInfo) {
            throw new LogicException(sprintf('找不到模块%s信息', $moduleName));
        }
        return new static($moduleInfo);
    }

    /**
     * @param array $moduleInfo 模块信息
     */
    public function __construct(protected array $moduleInfo)
    {
        $this->moduleName = $moduleInfo['name'];
    }

    /**
     * 安装模块权限
     * @return bool
     * @throws \Exception
     */
    public function install(): bool
    {
        $model = new Perm();
        $model->startTrans();
        try {
            //安装标记，用于区别未更新的菜单
            $installFlag = null;

            //从控制器注解获取权限结构
            if ($perms = $this->getControllerPerms()) {
                //设置新的安装标记，如果数据没有更新到新的$installFlag，表明是失效的数据，后面需要删除
                $installFlag = time();
                //保存到数据库
                $this->savePerm($perms, null, $installFlag);
            }

            //卸载没有找到权限结构或者失效的菜单(不调用模型事件)
            $this->uninstall($installFlag);

            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 卸载模块权限
     * @param string|null $notEqInstallFlag 卸载 install_flag 不同的菜单
     * @return void
     */
    public function uninstall(string $notEqInstallFlag = null): void
    {
        $where[] = ['module', '=', $this->moduleName];
        if ($notEqInstallFlag) {
            $where[] = ['install_flag', '<>', $notEqInstallFlag];
        }
        Perm::where($where)->delete();
    }

    /**
     * 启用
     * @return void
     */
    public function enable(): void
    {
        Perm::where('module', $this->moduleName)->update(['status' => 1]);
    }

    /**
     * 停用
     * @return void
     */
    public function disable(): void
    {
        Perm::where('module', $this->moduleName)->update(['status' => 0]);
    }

    public function reinstall(): void
    {
        Db::startTrans();
        try {
            $this->install();
            //更新role_perm表里，通过perm_hash与perm表的hash表关联,更新perm_hash.perm_id=perm.hash
            Db::name('system_role_perm')
                ->alias('rp')
                ->leftJoin('system_perm p', 'rp.perm_hash = p.hash')
                ->whereNotNull("p.id")
                ->exp('rp.perm_id', 'p.id')
                ->update();
            Db::commit();
        } catch (DbException $e) {
            Db::rollback();
            throw new LogicException('数据库操作失败:' . $e->getMessage());
        }
    }

    /**
     * 扫描控制器，获取权限结构
     * @return PermGroup[]|PermMenu[]|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \ReflectionException
     */
    public function getControllerPerms(): ?array
    {
        //获取默认控制器所在路径
        $scanDir = Module::path($this->moduleName) . 'controller' . DIRECTORY_SEPARATOR;
        $namespace = Module::namespace($this->moduleName) . 'controller\\';
        //定义了后台控制器时，获取后台控制器所在路径
        if (!empty($this->moduleInfo['admin_controller_dir'])) {
            $scanDir .= $this->moduleInfo['admin_controller_dir'] . DIRECTORY_SEPARATOR;
            if (!is_dir($scanDir)) {
                throw new LogicException(sprintf('后台控制器目录不存在:%s', $scanDir));
            }
            $namespace .= $this->moduleInfo['admin_controller_dir'] . '\\';
        }

        //扫描目录，找控制器类文件，并将文件名转化为类名
        $files = scandir($scanDir);
        $files = array_filter($files, fn($file) => preg_match('/^[A-Z][a-zA-Z0-9]+\.php$/', $file));
        if (!$files) return null;

        $controllers = array_map(fn($file) => $namespace . basename($file, '.php'), $files);
        //获取控制器的权限信息
        $perms = array_filter(
            array_map(fn($controller) => AnnotationParser::adminPerm($controller), $controllers)
        );

        //找上级group
        $group = [];
        foreach ($perms as $perm) {
            $groupName = !$perm->group ? 'main' : $perm->group;
            $group[$groupName]['children'][] = $perm;
        }

        //获取模块的权限分组信息
        $permGroup = $this->moduleInfo['admin_perm_group'] ?? [];
        foreach ($group as $groupName => $groupInfo) {
            $merge = [];
            //正则判断$groupName是不是 xxx:yyy:zzz格式，如果是，表示添加到其他模块的的group
            if (preg_match('/^[a-z]+:[a-z]+:[a-z]+$/i', $groupName)) {
                list($category, $moduleName, $name) = explode(':', $groupName);
                //去数据库里找这个字段
                $merge = Perm::where('category', $category)
                    ->where('module', $moduleName)
                    ->where('name', $name)
                    ->find();
                if (!$merge) throw new LogicException(sprintf('权限分组%s不存在', $groupName));
                $merge = $merge->visible([
                    'id', 'category', 'module', 'name', 'title', 'icon', 'hash'
                ])->toArray();
            } else {
                foreach ($permGroup as $permItem) {
                    if ($permItem['name'] === $groupName) {
                        $merge = $permItem;
                        break;
                    }
                }
            }
            if (!$merge) $merge = [
                'name'  => $groupName,
                'title' => $this->moduleInfo['title'],
            ];
            //合并分组信息
            $group[$groupName] = array_merge([
                'type'     => 'group',
                'module'   => $this->moduleName,
                'category' => 'system',
                'icon'     => 'Files'
            ], $groupInfo, $merge);
        }

        //把$group['root']['children']里的数据提升到$group下级
        if (isset($group['root'])) {
            $group = array_merge($group, $group['root']['children']);
            unset($group['root']);
        }

        return array_map(fn($perm) => is_array($perm) ? PermGroup::make($perm) : $perm, $group);
    }

    /**
     * 保存到数据库
     * @param PermGroup[]|PermMenu[] $perms
     * @param Perm|null $parentPerm
     * @param string|null $installFlag 本次安装标记，用于识别已失效的数据
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function savePerm(array $perms, Perm $parentPerm = null, string $installFlag = null): void
    {
        //安装标记，用于重装时，区别已失效的数据
        if (!$installFlag) {
            //随机生成一个数字
            $installFlag = Str::random(10, 1);
        }

        foreach ($perms as $perm) {
            $data = $perm->toArray();
            $data['pid'] = $parentPerm ? $parentPerm->id : 0;
            $data['status'] = 1;
            $data['install_flag'] = $installFlag;

            //查找是不是存在
            $tblPrem = Perm::where([
                'pid'  => $data['pid'],
                'hash' => $data['hash'],
            ])->find();

            //新建根权限
            if (!$tblPrem) {
                $tblPrem = Perm::create($data);
            } else if ($tblPrem->module === $this->moduleName) {
                //只更新当前模块的权限
                $tblPrem->save([
                    'title'        => $data['title'],
                    'icon'         => $data['icon'] ?? '',
                    'install_flag' => $installFlag
                ]);
            }

            //新建action类型权限
            if ($perm->type === 'menu' && count($perm->children) > 0) {
                $this->createActions($perm->children, $tblPrem, $installFlag);
            } else if ($perm->type === 'group' && count($perm->children) > 0) {
                $this->savePerm($perm->children, $tblPrem, $installFlag);
            }
        }
    }

    /**
     * 添加菜单行为组
     * @param PermAction[] $actions
     * @param Perm $parentPerm
     * @param string $installFlag
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function createActions(array $actions, Perm $parentPerm, string $installFlag): array
    {
        return array_map(function ($action) use ($parentPerm, $installFlag) {
            $where = [
                'pid'  => $parentPerm->getAttr('id'),
                'hash' => $action->hash,
            ];
            //找是不是存在
            if (!$perm = Perm::where($where)->find()) {
                //不存在就新增
                $perm = Perm::create(array_merge($where, $action->toArray(), [
                    //父级id路径
                    'pid_path'     => $parentPerm->getParents($parentPerm->getAttr('id'), true),
                    'status'       => 1,
                    'install_flag' => $installFlag
                ]));
            } else {
                //更新
                $perm->save([
                    'title'        => $action->title,
                    'install_flag' => $installFlag
                ]);
            }
            return $perm;
        }, $actions);
    }

}
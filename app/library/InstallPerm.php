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
            //先卸载旧菜单(不调用模型事件)
            $this->uninstall();

            //从控制器注解获取权限结构
            if ($perms = $this->getControllerPerms()) {
                //保存到数据库
                $this->savePerm($perms);
            }

            $model->commit();
        } catch (\Exception $e) {
            $model->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * 卸载模块权限
     * @return void
     */
    public function uninstall(): void
    {
        Perm::where('module', $this->moduleName)->delete();
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
                ->where("1=1")
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
            foreach ($permGroup as $permItem) {
                if ($permItem['name'] === $groupName) {
                    $merge = $permItem;
                    break;
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
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function savePerm(array $perms, Perm $parentPerm = null): void
    {
        foreach ($perms as $perm) {
            $data = $perm->toArray();
            $data['pid'] = $parentPerm ? $parentPerm->id : 0;
            $data['status'] = 1;

            //查找是不是存在
            $tblPrem = Perm::where([
                'pid'  => $data['pid'],
                'hash' => $data['hash'],
            ])->find();

            //新建根权限
            !$tblPrem && $tblPrem = Perm::create($data);

            //新建action类型权限
            if ($perm->type === 'menu' && count($perm->children) > 0) {
                $this->createActions($perm->children, $tblPrem);
            } else if ($perm->type === 'group' && count($perm->children) > 0) {
                $this->savePerm($perm->children, $tblPrem);
            }
        }
    }

    /**
     * 添加菜单行为组
     * @param Perm $parentPerm
     * @param PermAction[] $actions
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function createActions(array $actions, Perm $parentPerm): array
    {
        return array_map(function ($action) use ($parentPerm) {
            $where = [
                'pid'  => $parentPerm->getAttr('id'),
                'hash' => $action->hash,
            ];
            //找是不是存在
            if (!$perm = Perm::where($where)->find()) {
                //不存在就新增
                $perm = Perm::create(array_merge($where, $action->toArray(), [
                    //父级id路径
                    'pid_path' => $parentPerm->getParents($parentPerm->getAttr('id'), true),
                    'status'   => 1,
                ]));
            }
            return $perm;
        }, $actions);
    }

}
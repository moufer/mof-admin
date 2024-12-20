<?php

namespace app\model;

use mof\Model;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

class Perm extends Model
{
    protected $name = 'system_perm';

    protected array $searchFields = [
        'id'        => 'integer',
        'category'  => 'string',
        'module'    => 'string',
        'type'      => 'string',
        'title'     => ['string', 'op' => 'like'],
        'status'    => ['integer', 'zero' => true],
        'create_at' => ['datetime', 'op' => 'between'],
    ];

    public static function onAfterInsert(\think\Model $model): void
    {
        //设置pid_path
        $model->setAttr(
            'pid_path',
            $model->getParents($model->getAttr('pid'), true)
        );
        $model->save();
    }

    public static function onAfterUpdate(Perm $model): void
    {
        //检测pid是否改变，如果改变了，则更新下级的pid_path
        if ($model->isDirty('pid')) {
            $newPid = $model->getAttr('pid');
            $oldPid = $model->getOrigin('pid');
            $newPidPath = $model->getParents($newPid); //新的父级id路径
            $oldPidPath = $model->getParents($oldPid); //旧的父级id路径
            self::where('pid_path', 'like', $oldPidPath . '-%')
                ->update([
                    'pid_path' => Db::raw("REPLACE(pid_path, '{$oldPidPath}-', '{$newPidPath}-')")
                ]);
        }
    }

    public static function onAfterDelete(Perm $model): void
    {
        //删除菜单时，把菜单下的action也删除
        if ('menu' === $model->getAttr('type')) {
            //把下级全删了
            $model->deleteChildren();
        }
    }

    /**
     * 获取指定父模块下所有权限
     * @param string $expr
     * @param $returnTree bool 是否返回树形结构
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getAll(string $expr = 'module=admin', bool $returnTree = false): array
    {
        list($field, $value) = explode('=', $expr);
        $perms = self::where($field, $value)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        if ($returnTree) {
            $tree = new \mof\front\Tree($perms);
            $perms = $tree->getData(0, 'title');
        }
        return $perms;
    }

    /**
     * 根据提供的权限id集合，返回包含上级id的完整的权限权限集合
     * @param array $permIds
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function getCompletePermIds(array $permIds): array
    {
        $perms = self::where('id', 'in', $permIds)
            ->where('status', 1)
            ->select();
        //从$perms里取出pid_path
        $ids = [];
        $perms->each(function ($item) use (&$ids) {
            $ids[] = $item->getAttr('id');         //把自己的id也加进去
            $pidPath = $item->getAttr('pid_path'); //父级id路径
            if ($pidPath) {
                $ids = array_merge($ids, explode('-', $pidPath)); //把父级id路径也加进去
            }
        });
        //数组转换成数字后，去重
        $ids = array_map('intval', $ids);
        return array_values(array_unique($ids));
    }

    /**
     * 当前节点的所有父级节点id，格式：1-2-3
     */
    public function getParents($pid, $appendSelf = false): string
    {
        if ($pid) {
            $parents = [];
            while ($pid && $pid > 0) {
                //$pid插入数组$parents的头部
                array_unshift($parents, $pid);
                $m = self::find($pid);
                $pid = $m ? $m->pid : 0;
            }
            //添加自己
            if ($appendSelf) $parents[] = $this->getAttr('id');
            return implode('-', $parents);
        } else {
            return $appendSelf ? $this->getAttr('id') : '';
        }
    }

    /**
     * 更换上级
     * @return void
     */
    public function changeChildrenPidPath(): void
    {
        //新的父级id路径
        $newPidPath = $this->getParents($this->getAttr('pid'), true) . '-';
        //旧的父级id路径
        $oldPidPath = $this->getOrigin('pid_path') . '-';

        //查找下级并更新其pid_path
        self::whereLike('pid_path', "{$newPidPath}%")->update([
            'pid_path' => Db::raw("REPLACE(pid_path, '{$oldPidPath}', '{$newPidPath}')")
        ]);
    }

    /**
     * 删除自己的下级和引用自己的角色权限
     * @return void
     */
    public function deleteChildren(): void
    {
        //找到所有下级
        $ids = self::whereLike('pid_path', "%{$this->id}-%")->column('id');
        //去角色权限里找，并删除掉
        RolePerm::where('perm_id', 'in', array_merge([$this->id], $ids ?: []))->delete();
        //删除自己的下级
        $ids && self::where('id', 'in', $ids)->delete();
    }

    /**
     * 设置上级id
     * @param $value
     * @return int
     */
    public function setPidAttr($value): int
    {
        if (is_array($value)) {
            $value = array_pop($value);
        } else if ($value === '') {
            $value = 0;
        }
        //$this->setAttr('pid_path', $this->getParents($value, true));
        return $value;
    }

    /**
     * 设置模块
     * @param $value
     * @param $data
     * @return string
     */
    public function setModuleAttr($value, $data): string
    {
        //行为类型时，所属模块使用上级权限的模块
        if ('action' === $data['type']) {
            try {
                //获取上级id
                $pid = is_array($data['pid']) ? array_pop($data['pid']) : $data['pid'];
                $parent = !empty($pid) ? self::find($pid) : false;
                return $parent ? $parent->module : $value;
            } catch (DbException) {
                return $value;
            }
        }
        return $value;
    }

    protected function setIconAttr($value): string
    {
        return str_replace(' ', '_', $value);
    }

}
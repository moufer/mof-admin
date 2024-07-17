<?php
/**
 * Project: MofAdmin
 * Author: moufer <moufer@163.com>
 * Date: 2024/7/15 19:27
 */

namespace mof\utils;

use app\library\Perm;
use app\library\perm\PermAction;
use app\library\perm\PermMenu;
use mof\annotation\AdminPerm;
use mof\annotation\Description;
use mof\Module;
use ReflectionClass;
use ReflectionMethod;

class AnnotationParser
{
    /**
     * 解析控制器权限注解
     * @param string $controller
     * @return array|null
     * @throws \ReflectionException
     */
    public static function adminPerm(string $controller): ?\app\library\perm\Perm
    {
        $reflectionClass = new ReflectionClass($controller);
        $classAttributes = $reflectionClass->getAttributes(AdminPerm::class);
        if (!$classAttributes) return null;

        $commonData = [
            'name'   => $reflectionClass->getShortName(),
            'module' => Module::getNameByNameSpace($controller),
        ];
        $firstAction = '';

        $permMenu = PermMenu::make($commonData);
        foreach ($classAttributes as $classAttribute) {
            /** @var AdminPerm $adminPerm */
            $adminPerm = $classAttribute->newInstance();
            foreach (['title', 'icon', 'url', 'sort', 'group', 'category'] as $key) {
                $permMenu->$key = $adminPerm->$key;
            }

            $actions = explode(',', $adminPerm->actions);
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $actionName = $method->getName();
                //如果 $actionName 第一个字符不是英文字符，就跳过
                if (!preg_match('/^[a-zA-Z]/', $actionName)) continue;
                if (in_array("!$actionName", $actions)) continue;
                if (in_array($actionName, $actions) || in_array('*', $actions)) {
                    //记录第一个action
                    if (empty($firstAction)) $firstAction = $actionName;
                    $permAction = PermAction::make(array_merge($commonData, [
                        'name' => $actionName,
                        'perm' => $reflectionClass->getShortName() . '@' . $actionName,
                    ]));
                    //解析 action 的描述注解
                    $methodAttributes = $method->getAttributes(Description::class);
                    if (count($methodAttributes)) {
                        $permAction->title = $methodAttributes[0]->newInstance()->title;
                    }
                    $permMenu->addAction($permAction);
                }
            }
        }

        if ($firstAction) {
            $permMenu->perm = $reflectionClass->getShortName() . '@' . $firstAction;
        }

        return $permMenu;
    }

}
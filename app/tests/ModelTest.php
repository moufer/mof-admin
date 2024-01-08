<?php

namespace app\tests;

require_once __DIR__ . '/../../../vendor/autoload.php';
((new \think\App())->http)->run();

use app\model\Admin;
use app\model\Role;
use app\model\service\RoleService;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testMaker()
    {
        Role::maker(function(Role $model){
            $model->service('role', RoleService::class);
        });

        $model = Role::find(1);
        var_dump($model->id);
        $this->assertEquals($model->id, $model->service('role')->test());
    }

    public function testMacro()
    {
        Role::macro('hello', function(...$args) {
            var_dump(static::class,self::class);
            //var_dump($this);

        });
        $model = Role::find(1);
        $model->hello(1, 2, 3);
        Role::hello(1, 2, 3);
    }

}
<?php

namespace app\tests;

require_once __DIR__ . '/../../../vendor/autoload.php';
((new \think\App())->http)->run();

use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testSetPermission()
    {
        $role = \app\model\Role::find(7);
        $role->setPermission([36, 37]);

        $this->assertEquals(
            [36, 37],
            $role->perm_ids
        );
    }
}

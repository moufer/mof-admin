<?php

namespace app\tests\library;

use app\library\InstallPerm;
use PHPUnit\Framework\TestCase;

class InstallPermTest extends TestCase
{

    public function testInstall()
    {
        $module = 'system';
        $result = InstallPerm::make($module)->install();
        $this->assertEquals(true, $result);
    }

    public function testGetControllerPerms()
    {
        $module = 'document';
        $perms = InstallPerm::make($module)->getControllerPerms();
        foreach($perms as $perm) {
            var_export($perm->toArray());
        }
        $this->assertCount(2, $perms);
    }
}

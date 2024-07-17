<?php

namespace app\tests\library;

use mof\InstallModule;
use mof\Module;
use PHPUnit\Framework\TestCase;

class InstallModuleTest extends TestCase
{

    public function testCopyResource()
    {
        $module = 'article';
        InstallModule::make($module)->copyResource();
        $path = Module::getModuleResourcesPath($module, true);
        $exists = is_dir($path . '/wxapp');
        $this->assertEquals(true, $exists);
    }

    public function testRemoveResource()
    {
        $module = 'article';
        InstallModule::make($module)->removeResource();
        $path = Module::getModuleResourcesPath($module, true);
        $exists = is_dir($path . '/wxapp');
        $this->assertEquals(false, $exists);
    }
}

<?php

namespace app\tests;

require_once __DIR__ . '/../../../vendor/autoload.php';
((new \think\App())->http)->run();

use app\model\Perm;
use PHPUnit\Framework\TestCase;

class PermTest extends TestCase
{

//    public function testGetCompletePermIds()
//    {
//        //找差集
//        $diff = array_diff([28, 35, 36, 37], Perm::getCompletePermIds([36, 37]));
//        $this->assertEquals(
//            [], $diff);
//    }

    public function testGetParents()
    {
        $model  = new Perm();
        $string = $model->getParents(63);

        $this->assertEquals('63', $string);
    }
}

<?php

namespace app\tests;

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

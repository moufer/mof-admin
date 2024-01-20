<?php

namespace app\tests\model;

use app\model\Perm;
use PHPUnit\Framework\TestCase;


class PermTest extends TestCase
{

    public function testGetCompletePermIds()
    {
        $ids = Perm::getCompletePermIds([358,365,372]);
        var_dump($ids);
        //检测$ids里包含有（不是完全等于）[28,63,634]
        $this->assertEquals(true,
            in_array(28, $ids)
            && in_array(63, $ids)
            && in_array(234, $ids)
        );
    }


}

<?php

namespace Chronic\Tests;

class MiniDateTest extends \PHPUnit_Framework_TestCase
{
    function testValidMonth()
    {
        $this->setExpectedException('InvalidArgumentException');
        new \Chronic\MiniDate(0,12);
        new \Chronic\MiniDate(13,1);
    }

    function testIsBetween()
    {
        $m = new \Chronic\MiniDate(3,2);
        $this->assertTrue($m->isBetween(new \Chronic\MiniDate(2,4), new \Chronic\MiniDate(4,7)));
        $this->assertFalse($m->isBetween(new \Chronic\MiniDate(1,5), new \Chronic\MiniDate(2,7)));

        $m = new \Chronic\MiniDate(12,24);
        $this->assertFalse($m->isBetween(new \Chronic\MiniDate(10,1), new \Chronic\MiniDate(12,21)));
    }

    function testIsBetweenShortRange()
    {
        $m = new \Chronic\MiniDate(5,10);
        $this->assertTrue($m->isBetween(new \Chronic\MiniDate(5,3), new \Chronic\MiniDate(5,12)));
        $this->assertFalse($m->isBetween(new \Chronic\MiniDate(5,11), new \Chronic\MiniDate(5,15)));
    }

    function testIsBetweenWrappingRange()
    {
        $m = new \Chronic\MiniDate(1,1);
        $this->assertTrue($m->isBetween(new \Chronic\MiniDate(11,11), new \Chronic\MiniDate(2,2)));

        $m = new \Chronic\MiniDate(12,12);
        $this->assertTrue($m->isBetween(new \Chronic\MiniDate(11,11), new \Chronic\MiniDate(1,5)));
    }
}

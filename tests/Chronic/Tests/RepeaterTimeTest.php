<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Repeater\RepeaterTime;

date_default_timezone_set('America/Winnipeg');


class RepeaterTimeTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNextFuture()
    {
        $t = new RepeaterTime('4:00');
        $t->start($this->now);

        $this->assertEquals(getdate(mktime(16,0,0,8,16,2006)), $t->next('future')->begin());
        $this->assertEquals(getdate(mktime(4,0,0,8,17,2006)), $t->next('future')->begin());

        $t = new RepeaterTime('13:00');
        $t->start($this->now);

        $this->assertEquals(getdate(mktime(13,0,0,8,17,2006)), $t->next('future')->begin());
        $this->assertEquals(getdate(mktime(13,0,0,8,18,2006)), $t->next('future')->begin());

        $t = new RepeaterTime('0400');
        $t->start($this->now);

        $this->assertEquals(getdate(mktime(4,0,0,8,17,2006)), $t->next('future')->begin());
        $this->assertEquals(getdate(mktime(4,0,0,8,18,2006)), $t->next('future')->begin());
    }

    function testNextPast()
    {
        $t = new RepeaterTime('4:00');
        $t->start($this->now);

        $this->assertEquals(getdate(mktime(4,0,0,8,16,2006)), $t->next('past')->begin());
        $this->assertEquals(getdate(mktime(16,0,0,8,15,2006)), $t->next('past')->begin());

        $t = new RepeaterTime('13:00');
        $t->start($this->now);

        $this->assertEquals(getdate(mktime(13,0,0,8,16,2006)), $t->next('past')->begin());
        $this->assertEquals(getdate(mktime(13,0,0,8,15,2006)), $t->next('past')->begin());
    }

    function testType()
    {
        $t = new RepeaterTime('4');

        $type = $t->getType();

        $this->assertEquals(14400, $t->getType()->getTime());

        $t = new RepeaterTime('14');
        $this->assertEquals(50400, $t->getType()->getTime());

        $t = new RepeaterTime('4:00');
        $this->assertEquals(14400, $t->getType()->getTime());

        $t = new RepeaterTime('4:30');
        $this->assertEquals(16200, $t->getType()->getTime());

        $t = new RepeaterTime('1400');
        $this->assertEquals(50400, $t->getType()->getTime());

        $t = new RepeaterTime('0400');
        $this->assertEquals(14400, $t->getType()->getTime());

        $t = new RepeaterTime('04');
        $this->assertEquals(14400, $t->getType()->getTime());

        $t = new RepeaterTime('400');
        $this->assertEquals(14400, $t->getType()->getTime());
    }
}

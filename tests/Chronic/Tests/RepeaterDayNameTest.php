<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

date_default_timezone_set('America/Winnipeg');

class RepeaterDayNameTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testMatch()
    {
        $token = new \Chronic\Token('saturday');
        $repeater = \Chronic\Repeater::scan_for_day_names($token);
        $this->assertEquals('Chronic\Repeater\RepeaterDayName', get_class($repeater));
        $this->assertEquals('saturday', $repeater->getType());

        $token = new \Chronic\Token('sunday');
        $repeater = \Chronic\Repeater::scan_for_day_names($token);
        $this->assertEquals('Chronic\Repeater\RepeaterDayName', get_class($repeater));
        $this->assertEquals('sunday', $repeater->getType());
    }

    function testNextFuture()
    {
        $mondays = new \Chronic\Repeater\RepeaterDayName('monday');
        $mondays->start($this->now);

        $span = $mondays->next('future');

        $this->assertEquals(getdate(mktime(0,0,0,8,21,2006)), $span->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,22,2006)), $span->end());

        $span = $mondays->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,8,28,2006)), $span->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,29,2006)), $span->end());
    }

    function testNextPast()
    {
        $mondays = new \Chronic\Repeater\RepeaterDayName('monday');
        $mondays->start($this->now);

        $span = $mondays->next('past');

        $this->assertEquals(getdate(mktime(0,0,0,8,14,2006)), $span->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,15,2006)), $span->end());

        $span = $mondays->next('past');

        $this->assertEquals(getdate(mktime(0,0,0,8,7,2006)), $span->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,8,2006)), $span->end());
    }
}

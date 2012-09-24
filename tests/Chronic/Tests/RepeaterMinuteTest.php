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

class RepeaterMinuteTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(7, 15, 30, 6, 25, 2008));
    }

    function testNextFuture()
    {
        $minutes = new \Chronic\Repeater\RepeaterMinute('minute');
        $minutes->start($this->now);

        $next_minute = $minutes->next('future');
        $this->assertEquals(getdate(mktime(7,16,0,6,25,2008)), $next_minute->begin());
        $this->assertEquals(getdate(mktime(7,17,0,6,25,2008)), $next_minute->end());

        $next_next_minute = $minutes->next('future');
        $this->assertEquals(getdate(mktime(7,17,0,6,25,2008)), $next_next_minute->begin());
        $this->assertEquals(getdate(mktime(7,18,0,6,25,2008)), $next_next_minute->end());
    }

    function testNextPast()
    {
        $minutes = new \Chronic\Repeater\RepeaterMinute('minute');
        $minutes->start($this->now);

        $prev_minute = $minutes->next('past');
        $this->assertEquals(getdate(mktime(7,14,0,6,25,2008)), $prev_minute->begin());
        $this->assertEquals(getdate(mktime(7,15,0,6,25,2008)), $prev_minute->end());

        $prev_prev_minute = $minutes->next('past');
        $this->assertEquals(getdate(mktime(7,13,0,6,25,2008)), $prev_prev_minute->begin());
        $this->assertEquals(getdate(mktime(7,14,0,6,25,2008)), $prev_prev_minute->end());
    }
}

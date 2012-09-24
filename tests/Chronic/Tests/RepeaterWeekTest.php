<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Repeater\RepeaterWeek;

date_default_timezone_set('America/Winnipeg');

class RepeaterWeekTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNextFuture()
    {
        $weeks = new RepeaterWeek('week');
        $weeks->start($this->now);

        $next_week = $weeks->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,8,20,2006)), $next_week->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,27,2006)), $next_week->end());

        $next_next_week = $weeks->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,8,27,2006)), $next_next_week->begin());
        $this->assertEquals(getdate(mktime(0,0,0,9,3,2006)), $next_next_week->end());
    }

    function testNextPast()
    {
        $weeks = new RepeaterWeek('week');
        $weeks->start($this->now);

        $last_week = $weeks->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,8,6,2006)), $last_week->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,13,2006)), $last_week->end());

        $last_last_week = $weeks->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,7,30,2006)), $last_last_week->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,6,2006)), $last_last_week->end());
    }

    function testThisFuture()
    {
        $weeks = new RepeaterWeek('week');
        $weeks->start($this->now);

        $this_week = $weeks->this('future');
        $this->assertEquals(getdate(mktime(15,0,0,8,16,2006)), $this_week->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,20,2006)), $this_week->end());
    }

    function testThisPast()
    {
        $weeks = new RepeaterWeek('week');
        $weeks->start($this->now);

        $this_week = $weeks->this('past');
        $this->assertEquals(getdate(mktime(0,0,0,8,13,2006)), $this_week->begin());
        $this->assertEquals(getdate(mktime(14,0,0,8,16,2006)), $this_week->end());
    }

    function testOffset()
    {
        $span = new \Chronic\Span($this->now, getdate($this->now[0] +  1));
        $repeater = new RepeaterWeek('week');
        $offset_span = $repeater->offset($span, 3, 'future');

        $this->assertEquals(getdate(mktime(14,0,0,9,6,2006)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(14,0,1,9,6,2006)), $offset_span->end());
    }
}

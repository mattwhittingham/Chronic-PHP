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

class RepeaterHourTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNextFuture()
    {
        $hours = new \Chronic\Repeater\RepeaterHour('hour');
        $hours->start($this->now);

        $next_hour = $hours->next('future');
        $this->assertEquals(getdate(mktime(15,0,0,8,16,2006)), $next_hour->begin());
        $this->assertEquals(getdate(mktime(16,0,0,8,16,2006)), $next_hour->end());

        $next_next_hour = $hours->next('future');
        $this->assertEquals(getdate(mktime(16,0,0,8,16,2006)), $next_next_hour->begin());
        $this->assertEquals(getdate(mktime(17,0,0,8,16,2006)), $next_next_hour->end());
    }

    function testNextPast()
    {
        $hours = new \Chronic\Repeater\RepeaterHour('hour');
        $hours->start($this->now);

        $past_hour = $hours->next('past');
        $this->assertEquals(getdate(mktime(13,0,0,8,16,2006)), $past_hour->begin());
        $this->assertEquals(getdate(mktime(14,0,0,8,16,2006)), $past_hour->end());

        $past_past_hour = $hours->next('past');
        $this->assertEquals(getdate(mktime(12,0,0,8,16,2006)), $past_past_hour->begin());
        $this->assertEquals(getdate(mktime(13,0,0,8,16,2006)), $past_past_hour->end());
    }

    function testThis()
    {
        $this->now = getdate(mktime(14, 30, 0, 8, 16, 2006));

        $hours = new \Chronic\Repeater\RepeaterHour('hour');
        $hours->start($this->now);

        $this_hour = $hours->this('future');
        $this->assertEquals(getdate(mktime(14,31,0,8,16,2006)), $this_hour->begin());
        $this->assertEquals(getdate(mktime(15,0,0,8,16,2006)), $this_hour->end());

        $this_hour = $hours->this('past');
        $this->assertEquals(getdate(mktime(14,0,0,8,16,2006)), $this_hour->begin());
        $this->assertEquals(getdate(mktime(14,30,0,8,16,2006)), $this_hour->end());

        $this_hour = $hours->this('none');
        $this->assertEquals(getdate(mktime(14,0,0,8,16,2006)), $this_hour->begin());
        $this->assertEquals(getdate(mktime(15,0,0,8,16,2006)), $this_hour->end());
    }

    function testOffset()
    {
        $span = new \Chronic\Span($this->now, getdate($this->now[0] + 1));

        $repeater = new \Chronic\Repeater\RepeaterHour('hour');
        $offset_span = $repeater->offset($span, 3, 'future');

        $this->assertEquals(getdate(mktime(17,0,0,8,16,2006)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(17,0,1,8,16,2006)), $offset_span->end());

        $repeater = new \Chronic\Repeater\RepeaterHour('hour');
        $offset_span = $repeater->offset($span, 24, 'past');

        $this->assertEquals(getdate(mktime(14,0,0,8,15,2006)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(14,0,1,8,15,2006)), $offset_span->end());
    }
}

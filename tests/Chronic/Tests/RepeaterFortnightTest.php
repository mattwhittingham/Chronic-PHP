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

class RepeaterFortnightTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNextFuture()
    {
        $fortnights = new \Chronic\Repeater\RepeaterFortnight('fortnight');
        $fortnights->start($this->now);

        $next_fortnight = $fortnights->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,8,20,2006)), $next_fortnight->begin());
        $this->assertEquals(getdate(mktime(0,0,0,9,3,2006)), $next_fortnight->end());

        $next_next_fortnight = $fortnights->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,9,3,2006)), $next_next_fortnight->begin());
        $this->assertEquals(getdate(mktime(0,0,0,9,17,2006)), $next_next_fortnight->end());
    }

    function testNextPast()
    {
        $fortnights = new \Chronic\Repeater\RepeaterFortnight('fortnight');
        $fortnights->start($this->now);

        $last_fortnight = $fortnights->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,7,30,2006)), $last_fortnight->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,13,2006)), $last_fortnight->end());

        $last_last_fortnight = $fortnights->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,7,16,2006)), $last_last_fortnight->begin());
        $this->assertEquals(getdate(mktime(0,0,0,7,30,2006)), $last_last_fortnight->end());
    }

    function testThisFuture()
    {
        $fortnights = new \Chronic\Repeater\RepeaterFortnight('fortnight');
        $fortnights->start($this->now);

        $this_fortnight = $fortnights->this('future');
        $this->assertEquals(getdate(mktime(15,0,0,8,16,2006)), $this_fortnight->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,27,2006)), $this_fortnight->end());
    }

    function testThisPast()
    {
        $fortnights = new \Chronic\Repeater\RepeaterFortnight('fortnight');
        $fortnights->start($this->now);

        $this_fortnight = $fortnights->this('past');
        $this->assertEquals(getdate(mktime(0,0,0,8,13,2006)), $this_fortnight->begin());
        $this->assertEquals(getdate(mktime(14,0,0,8,16,2006)), $this_fortnight->end());
    }

    function testOffset()
    {
        $span = new \Chronic\Span($this->now, getdate($this->now[0] +  1));
        $repeater = new \Chronic\Repeater\RepeaterWeek('week');
        $offset_span = $repeater->offset($span, 3, 'future');

        $this->assertEquals(getdate(mktime(14,0,0,9,6,2006)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(14,0,1,9,6,2006)), $offset_span->end());
    }
}

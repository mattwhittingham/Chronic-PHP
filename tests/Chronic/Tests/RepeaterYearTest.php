<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Repeater\RepeaterYear;

date_default_timezone_set('America/Winnipeg');

class RepeaterYearTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNextFuture()
    {
        $years = new RepeaterYear('year');
        $years->start($this->now);

        $next_year = $years->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2007)), $next_year->begin());
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2008)), $next_year->end());

        $next_next_year = $years->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2008)), $next_next_year->begin());
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2009)), $next_next_year->end());
    }

    function testNextPast()
    {
        $years = new RepeaterYear('year');
        $years->start($this->now);

        $last_year = $years->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2005)), $last_year->begin());
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2006)), $last_year->end());

        $last_last_year = $years->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2004)), $last_last_year->begin());
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2005)), $last_last_year->end());
    }

    function testThis()
    {
        $years = new RepeaterYear('year');
        $years->start($this->now);

        $this_year = $years->this('future');
        $this->assertEquals(getdate(mktime(0,0,0,8,17,2006)), $this_year->begin());
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2007)), $this_year->end());

        $this_year = $years->this('past');
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2006)), $this_year->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,16,2006)), $this_year->end());
    }

    function testOffset()
    {
        $span = new \Chronic\Span($this->now, getdate($this->now[0] +  1));

        $repeater = new RepeaterYear('year');
        $offset_span = $repeater->offset($span, 3, 'future');

        $this->assertEquals(getdate(mktime(14,0,0,8,16,2009)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(14,0,1,8,16,2009)), $offset_span->end());
    }
}

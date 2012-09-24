<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wt'pastPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Repeater\RepeaterMonth;

date_default_timezone_set('America/Winnipeg');

class RepeaterMonthTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
       $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testOffsetBy()
    {
        $repeater = new RepeaterMonth('month');

        // future
        $time = $repeater->offset_by($this->now, 1, 'future');
        $this->assertEquals(getdate(mktime(14, 0, 0, 9, 16, 2006)), $time);

        $time = $repeater->offset_by($this->now, 5, 'future');
        $this->assertEquals(getdate(mktime(14, 0, 0, 1, 16, 2007)), $time);

        // past
        $time = $repeater->offset_by($this->now, 1, 'past');
        $this->assertEquals(getdate(mktime(14, 0, 0, 7, 16, 2006)), $time);

        $time = $repeater->offset_by($this->now, 10, 'past');
        $this->assertEquals(getdate(mktime(14, 0, 0, 10, 16, 2005)), $time);

        $time = $repeater->offset_by(getdate(mktime(0, 0, 0, 3, 29, 2010)), 1, 'past');

        $this->assertEquals(2, $time['mon']);
        $this->assertEquals(28, $time['mday']);
    }

    function testOffset()
    {
        $span = new \Chronic\Span($this->now, getdate($this->now[0] + 60));
        $repeater = new RepeaterMonth('month');

        // future
        $offset_span = $repeater->offset($span, 1, 'future');

        $this->assertEquals(getdate(mktime(14, 0, 0, 9, 16, 2006)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(14, 1, 0, 9, 16, 2006)), $offset_span->end());

        // past
        $offset_span = $repeater->offset($span, 1, 'past');

        $this->assertEquals(getdate(mktime(14, 0, 0 , 7, 16, 2006)), $offset_span->begin());
        $this->assertEquals(getdate(mktime(14, 1, 0 , 7, 16, 2006)), $offset_span->end());
    }
}

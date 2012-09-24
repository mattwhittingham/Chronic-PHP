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

class SpanTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testSpanWidth()
    {
        $span = new \Chronic\Span(getdate(mktime(0,0,0,8,16,2006)), getdate(mktime(0,0,0,8,17,2006)));
        $this->assertEquals(60 * 60 * 24, $span->width());
    }

    function testSpanMath()
    {
        $span = new \Chronic\Span(getdate(1), getdate(2));
        $this->assertEquals(getdate(2), $span->add(1)->begin());
        $this->assertEquals(getdate(3), $span->add(1)->end());
        $this->assertEquals(getdate(0), $span->subtract(1)->begin());
        $this->assertEquals(getdate(1), $span->subtract(1)->end());
    }
}

<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use \Chronic;
use \Chronic\Repeater\RepeaterDayName;
use \Chronic\Repeater\RepeaterDayPortion;
use \Chronic\Repeater\RepeaterMonthName;
use \Chronic\ScalarDay;


date_default_timezone_set('America/Winnipeg');

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function test_handle_class_1()
    {
        $handler = new Chronic\Handler(array(':repeater'), ':handler');

        $tokens = array(new Chronic\Token('friday'));
        $tokens[0]->tag(new RepeaterDayName(':friday'));

        $this->assertTrue($handler->match($tokens, Chronic::definitions()));

        $tokens[] = new Chronic\Token('afternoon');
        $tokens[1]->tag(new RepeaterDayPortion(':afternoon'));

        $this->assertFalse($handler->match($tokens, Chronic::definitions()));
    }

    function test_handle_class_2()
    {
        $handler = new Chronic\Handler(array(':repeater', ':repeater?'), ':handler');

        $tokens = array(new Chronic\Token('friday'));
        $tokens[0]->tag(new RepeaterDayName(':friday'));

        $this->assertTrue($handler->match($tokens, Chronic::definitions()));

        $tokens[] = new Chronic\Token('afternoon');
        $tokens[1]->tag(new RepeaterDayPortion(':afternoon'));

        $this->assertTrue($handler->match($tokens, Chronic::definitions()));

        $tokens[] = new Chronic\Token('afternoon');
        $tokens[2]->tag(new RepeaterDayPortion(':afternoon'));

        $this->assertFalse($handler->match($tokens, Chronic::definitions()));
    }

    function test_handle_class_3()
    {
        $handler = new Chronic\Handler(array(':repeater', ':time?'), ':handler');

        $tokens = array(new Chronic\Token('friday'));
        $tokens[0]->tag(new RepeaterDayName(':friday'));

        $this->assertTrue($handler->match($tokens, Chronic::definitions()));

        $tokens[] = new Chronic\Token('afternoon');
        $tokens[1]->tag(new RepeaterDayPortion(':afternoon'));

        $this->assertFalse($handler->match($tokens, Chronic::definitions()));
    }

    function test_handle_class_4()
    {
        $handler = new Chronic\Handler(array(':repeater_month_name', ':scalar_day', ':time?'), ':handler');

        $tokens = array(new Chronic\Token('may'));
        $tokens[0]->tag(new RepeaterMonthName(':may'));

        $this->assertFalse($handler->match($tokens, Chronic::definitions()));

        $tokens[] = new Chronic\Token('27');
        $tokens[1]->tag(new ScalarDay(27));

        $this->assertTrue($handler->match($tokens, Chronic::definitions()));
    }

    function test_handle_class_5()
    {
        $this->markTestIncomplete();
    }

    function test_handle_class_6()
    {
        $this->markTestIncomplete();
    }

    function test_handle_class_7()
    {
        $this->markTestIncomplete();
    }
}

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

    // tokens << Chronic::Token.new('afternoon')
    // tokens[1].tag(Chronic::RepeaterDayPortion.new(:afternoon))

    // assert !handler.match(tokens, Chronic.definitions)
        $this->markTestIncomplete();
    }

    function test_handle_class_2()
    {

        $this->markTestIncomplete();
    }

    function test_handle_class_3()
    {
        $this->markTestIncomplete();
    }

    function test_handle_class_4()
    {
        $this->markTestIncomplete();
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

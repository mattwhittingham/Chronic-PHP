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

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

class ParsingTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }


    function test_handle_rmn_sd()
    {
        $time = $this->parseNow('aug 3');
        $this->assertEquals(getdate(mktime(12,0,0,8,3,2006)), $time);
    }

    private function parseNow($str, $options = array())
    {
        return Chronic::parse($str, array_merge(array('now' => $this->now[0]), $options));
    }
}

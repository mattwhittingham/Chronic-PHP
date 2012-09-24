<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Repeater\RepeaterMonthName;

date_default_timezone_set('America/Winnipeg');

class RepeaterMonthNameTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNext()
    {
        // future
        $mays = new RepeaterMonthName('may');
        $mays->start($this->now);

        $next_may = $mays->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,5,1,2007)), $next_may->begin());
        $this->assertEquals(getdate(mktime(0,0,0,6,1,2007)), $next_may->end());

        $next_next_may = $mays->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,5,1,2008)), $next_next_may->begin());
        $this->assertEquals(getdate(mktime(0,0,0,6,1,2008)), $next_next_may->end());

        $decembers = new RepeaterMonthName('december');
        $decembers->start($this->now);

        $next_december = $decembers->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,12,1,2006)), $next_december->begin());
        $this->assertEquals(getdate(mktime(0,0,0,1,1,2007)), $next_december->end());

        // past
        $mays = new RepeaterMonthName('may');
        $mays->start($this->now);

        $this->assertEquals(getdate(mktime(0,0,0,5,1,2006)), $mays->next('past')->begin());
        $this->assertEquals(getdate(mktime(0,0,0,5,1,2005)), $mays->next('past')->begin());
    }

    function testThis()
    {
        $octobers = new RepeaterMonthName('october');
        $octobers->start($this->now);

        $this_october = $octobers->this('future');
        $this->assertEquals(getdate(mktime(0,0,0,10,1,2006)), $this_october->begin());
        $this->assertEquals(getdate(mktime(0,0,0,11,1,2006)), $this_october->end());

        $aprils = new RepeaterMonthName('april');
        $aprils->start($this->now);

        $this_april = $aprils->this('past');
        $this->assertEquals(getdate(mktime(0,0,0,4,1,2006)), $this_april->begin());
        $this->assertEquals(getdate(mktime(0,0,0,5,1,2006)), $this_april->end());
    }
}

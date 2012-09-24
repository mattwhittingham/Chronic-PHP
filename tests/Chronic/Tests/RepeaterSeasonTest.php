<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Repeater\RepeaterSeason;

date_default_timezone_set('America/Winnipeg');

class RepeaterSeasonTest extends \PHPUnit_Framework_TestCase
{
    public $now;

    function setUp()
    {
        $this->now = getdate(mktime(14, 0, 0, 8, 16, 2006));
    }

    function testNextFuture()
    {
        $seasons = new RepeaterSeason('season');
        $seasons->start($this->now);

        $next_season = $seasons->next('future');
        $this->assertEquals(getdate(mktime(0,0,0,9,23,2006)), $next_season->begin());
        $this->assertEquals(getdate(mktime(0,0,0,12,21,2006)), $next_season->end());
    }

    function testNextPast()
    {
        $seasons = new RepeaterSeason('season');
        $seasons->start($this->now);

        $last_season = $seasons->next('past');
        $this->assertEquals(getdate(mktime(0,0,0,3,20,2006)), $last_season->begin());
        $this->assertEquals(getdate(mktime(0,0,0,6,20,2006)), $last_season->end());
    }

    function testThis()
    {
        $seasons = new RepeaterSeason('season');
        $seasons->start($this->now);

        $this_season = $seasons->this('future');
        $this->assertEquals(getdate(mktime(0,0,0,8,17,2006)), $this_season->begin());
        $this->assertEquals(getdate(mktime(0,0,0,9,22,2006)), $this_season->end());

        $this_season = $seasons->this('past');
        $this->assertEquals(getdate(mktime(0,0,0,6,21,2006)), $this_season->begin());
        $this->assertEquals(getdate(mktime(0,0,0,8,16,2006)), $this_season->end());
    }
}

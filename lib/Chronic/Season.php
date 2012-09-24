<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class Season
{
    private $_start;
    private $_end;

    public function __construct($start_date, $end_date)
    {
        $this->_start = $start_date;
        $this->_end = $end_date;
    }

    public static function findNextSeason($season, $pointer)
    {
        $lookup = array('spring', 'summer', 'autumn', 'winter');
        $amount = array_search($season, $lookup) + 1 * $pointer;
        $next_season_num = $amount - floor($amount / 4) * 4;
        return $lookup[$next_season_num];
    }

    public static function seasonAfter($season)
    {
        return self::findNextSeason($season, 1);
    }

    public static function seasonBefore($season)
    {
        return self::findNextSeason($season, -1);
    }

    public function getStart()
    {
        return $this->_start;
    }

    public function getEnd()
    {
        return $this->_end;
    }
}

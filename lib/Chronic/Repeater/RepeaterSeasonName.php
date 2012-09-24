<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterSeasonName extends \Chronic\Repeater\RepeaterSeason
{
    const SEASON_SECONDS = 7862400; // 91 * 24 * 60 * 60
    const DAY_SECONDS = 86400; // 24 * 60 * 60

    public function next($pointer)
    {
        $direction = $pointer === 'future' ? 1 : -1;
        $this->findNextSeasonSpan($direction, $this->_type);
    }

    public function this($pointer = 'future')
    {
        $direction = $pointer === 'future' ? 1 : -1;

        $today = \Chronic::construct($this->_now['year'], $this->_now['mon'], $this->_now['mday']);
        $goal_ssn_start = getdate($today[0] + $direction * $this->numSecondsTilStart($this->_type, $direction));
        $goal_ssn_end = getdate($today[0] + $direction * $this->numSecondsTilEnd($this->_type, $direction));
        $curr_ssn = $this->findCurrentSeason(\Chronic\MiniDate::fromTime($this->_now));

        if($pointer == 'past')
        {
            $this_ssn_start = $goal_ssn_start;
            $this_ssn_end = $curr_ssn === $this->_type ? $today : $goal_ssn_end;
        }
        elseif($pointer == 'future')
        {
            $this_ssn_start = $curr_ssn === $this->_type ? getdate($today[0] + RepeaterDay::DAY_SECONDS) : $goal_ssn_start;
            $this_ssn_end = $goal_ssn_end;
        }
        elseif($pointer == 'none')
        {
            $this_ssn_start = $goal_ssn_start;
            $this_ssn_end = $goal_ssn_end;
        }

        return $this->constructSeason($this_ssn_start, $this_ssn_end);
    }

    public function offset($span, $amount, $pointer)
    {
        return new \Chronic\Span($this->offsetBy($span->begin(), $amount, $pointer), $this->offsetBy($span->end(), $amount, $pointer));
    }

    public function offsetBy($time, $amount, $pointer)
    {
        $direction = $pointer === 'future' ? 1 : -1;
        return $time[0] + $amount * $direction * RepeaterYear::YEAR_SECONDS;
    }
}

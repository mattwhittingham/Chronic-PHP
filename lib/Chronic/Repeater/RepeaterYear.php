<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterYear extends \Chronic\Repeater
{
    const YEAR_SECONDS = 31536000; // 364 * 24 * 60 * 60

    public $current_year_start;

    public function next($pointer)
    {
        if ( ! $this->current_year_start)
        {
            if($pointer === 'future')
                $this->current_year_start = \Chronic::construct($this->now['year'] + 1);
            elseif($pointer === 'past')
                $this->current_year_start = \Chronic::construct($this->now['year'] - 1);
        }
        else
        {
            $diff = $pointer == 'future' ? 1 : -1;
            $this->current_year_start = \Chronic::construct($this->current_year_start['year'] + $diff);
        }

        return new \Chronic\Span($this->current_year_start, \Chronic::construct($this->current_year_start['year'] + 1));
    }

    public function this($pointer = 'future')
    {
        if($pointer ==='future')
        {
            $this_year_start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'] + 1);
            $this_year_end = \Chronic::construct($this->now['year'] + 1, 1, 1);
        }
        elseif($pointer === 'past')
        {
            $this_year_start = \Chronic::construct($this->now['year'], 1, 1);
            $this_year_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
        }
        elseif($pointer === 'none')
        {
            $this_year_start = \Chronic::construct($this->now['year'], 1, 1);
            $this_year_end = \Chronic::construct($this->now['year'] + 1, 1, 1);
        }

        return new \Chronic\Span($this_year_start, $this_year_end);
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        $new_begin = $this->buildOffsetTime($span->begin(), $amount, $direction);
        $new_end = $this->buildOffsetTime($span->end(), $amount, $direction);

        return new \Chronic\Span($new_begin, $new_end);
    }

    public function width()
    {
        return self::YEAR_SECONDS;
    }

    public function __toString()
    {
        parent::__toString().'-year';
    }

    private function buildOffsetTime($time,$amount, $direction)
    {
        $year = $time['year'] + ($amount * $direction);
        $days = $this->monthDays($year, $time['mon']);
        $day = $time['mday'] > $days ? $days : $time['mday'];
        return \Chronic::construct($year, $time['mon'], $day, $time['hours'], $time['minutes'], $time['seconds']);
    }

    private function monthDays($year, $month)
    {
        if(date('L', mktime(0,0,0,0,0, $year)))
            return RepeaterMonth::$MONTH_DAYS_LEAP[$month - 1];
        else
            return RepeaterMonth::$MONTH_DAYS[$month - 1];
    }
}

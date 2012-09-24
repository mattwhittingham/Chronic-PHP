<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterSeason extends \Chronic\Repeater
{
    const SEASON_SECONDS = 7862400; // 91 * 24 * 60 * 60
    public $_next_season_start;
    public $_next_season_end;

    public static function SEASONS()
    {
        return array(
            'spring' => new \Chronic\Season(new \Chronic\MiniDate(3,20), new \Chronic\MiniDate(6,20)),
            'summer' => new \Chronic\Season(new \Chronic\MiniDate(6,21), new \Chronic\MiniDate(9,22)),
            'autumn' => new \Chronic\Season(new \Chronic\MiniDate(9,23), new \Chronic\MiniDate(12,21)),
            'winter' => new \Chronic\Season(new \Chronic\MiniDate(12,22), new \Chronic\MiniDate(3,19)),
        );
    }

    public function next($pointer)
    {
        $direction = $pointer === 'future' ? 1 : -1;
        $next_season = \Chronic\Season::findNextSeason($this->findCurrentSeason(\Chronic\MiniDate::fromTime($this->now)), $direction);

        return $this->findNextSeasonSpan($direction, $next_season);
    }

    public function this($pointer = 'future')
    {
        $direction = $pointer === 'future' ? 1 : -1;

        $today = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
        $this_ssn = $this->findCurrentSeason(\Chronic\MiniDate::fromTime($this->now));

        if($pointer == 'past')
        {
            $this_ssn_start = getdate($today[0] + $direction * $this->numSecondsTilStart($this_ssn, $direction));
            $this_ssn_end = $today;
        }
        elseif($pointer == 'future')
        {
            $this_ssn_start = getdate($today[0] + RepeaterDay::DAY_SECONDS);
            $this_ssn_end = getdate($today[0] + $direction * $this->numSecondsTilEnd($this_ssn, $direction));
        }
        elseif($pointer == 'none')
        {
            $this_ssn_start = getdate($today[0] + $direction * $this->numSecondsTilStart($this_ssn, $direction));
            $this_ssn_end = getdate($today[0] + $direction * $this->numSecondsTilEnd($this_ssn, $direction));
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
        return $time[0] + $amount * $direction * self::SEASON_SECONDS;
    }

    public function width()
    {
        return self::SEASON_SECONDS;
    }

    public function __toString()
    {
        parent::__toString().'-season';
    }

    protected function findNextSeasonSpan($direction, $next_season)
    {
        if( ! ( $this->_next_season_start || $this->_next_season_end))
        {
            $this->_next_season_start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
            $this->_next_season_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
        }

        $this->_next_season_start = getdate($this->_next_season_start[0] + $direction * $this->numSecondsTilStart($next_season, $direction));
        $this->_next_season_end = getdate($this->_next_season_end[0] + $direction * $this->numSecondsTilEnd($next_season, $direction));

        return $this->constructSeason($this->_next_season_start, $this->_next_season_end);
    }

    protected function findCurrentSeason(\Chronic\MiniDate $md)
    {
        $seasons = self::SEASONS();
        foreach(array('spring', 'summer', 'autumn', 'winter') as $season)
        {
            if($md->isBetween($seasons[$season]->getStart(), $seasons[$season]->getEnd()))
               return $season;
        }
    }

    protected function numSecondsTil($goal, $direction)
    {
        $start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
        $seconds = 0;

        while ( ! \Chronic\MiniDate::fromTime(getdate($start[0] + $direction * $seconds))->equals($goal))
        {
            $seconds += RepeaterDay::DAY_SECONDS;
        }

        return $seconds;
    }

    protected function numSecondsTilStart($season_symbol, $direction)
    {
        $seasons = self::SEASONS();
        return $this->numSecondsTil($seasons[$season_symbol]->getStart(), $direction);
    }

    protected function numSecondsTilEnd($season_symbol, $direction)
    {
        $seasons = self::SEASONS();
        return $this->numSecondsTil($seasons[$season_symbol]->getEnd(), $direction);
    }

    protected function constructSeason($start, $finish)
    {
        return new \Chronic\Span(
            \Chronic::construct($start['year'], $start['mon'], $start['mday']),
            \Chronic::construct($finish['year'], $finish['mon'], $finish['mday'])
        );
    }


}

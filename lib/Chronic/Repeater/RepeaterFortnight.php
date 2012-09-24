<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

use Chronic\Repeater\RepeaterDayName,
    Chronic\Repeater\RepeaterDay,
    Chronic\Repeater\RepeaterHour;

class RepeaterFortnight extends \Chronic\Repeater
{
    const FORTNIGHT_SECONDS = 1209600; // 14 * 24 * 60 * 60

    public $current_fortnight_start;

    public function next($pointer)
    {
        if ( ! $this->current_fortnight_start)
        {
            if($pointer == 'future')
            {
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $next_sunday_span = $sunday_repeater->next('future');
                $begin = $next_sunday_span->begin();
                $this->current_fortnight_start = $begin[0];
            }
            elseif($pointer == 'past')
            {
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start(getdate($this->now[0] + RepeaterDay::DAY_SECONDS));
                $sunday_repeater->next('past');
                $sunday_repeater->next('past');
                $last_sunday_span = $sunday_repeater->next('past');
                $begin = $last_sunday_span->begin();
                $this->current_fortnight_start = $begin[0];
            }
        }
        else
        {
            $direction = $pointer == 'future' ? 1 : -1;
            $this->current_fortnight_start += $direction * self::FORTNIGHT_SECONDS;
        }

        return new \Chronic\Span(getdate($this->current_fortnight_start), getdate($this->current_fortnight_start + self::FORTNIGHT_SECONDS));
    }

    public function this($pointer = 'future')
    {
        if($pointer == 'none')
            $pointer = 'future';

        switch($pointer)
        {
            case 'future':
                $tmp_now = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours']);
                $this_fortnight_start = getdate($tmp_now[0] + RepeaterHour::HOUR_SECONDS);
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $sunday_repeater->this('future');
                $this_sunday_span = $sunday_repeater->this('future');
                $this_fortnight_end = $this_sunday_span->begin();

                return new \Chronic\Span($this_fortnight_start, $this_fortnight_end);
            case 'past':
                $this_fortnight_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours']);
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $last_sunday_span = $sunday_repeater->next('past');
                $this_fortnight_start = $last_sunday_span->begin();

                return new \Chronic\Span($this_fortnight_start, $this_fortnight_end);
        }
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        return $span->add($direction * $amount * self::FORTNIGHT_SECONDS);
    }

    public function width()
    {
        return self::FORTNIGHT_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-fortnight';
    }
}

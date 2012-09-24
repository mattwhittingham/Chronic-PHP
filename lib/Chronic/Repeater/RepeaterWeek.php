<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterWeek extends \Chronic\Repeater
{
    const WEEK_SECONDS = 604800; // 7 * 24 * 60 * 60

    public $_current_week_start;

    public function next($pointer)
    {
        if ( ! $this->_current_week_start)
        {
            if($pointer == 'future')
            {
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $next_sunday_span = $sunday_repeater->next('future');
                $begin = $next_sunday_span->begin();
                $this->_current_week_start = $begin[0];
            }
            elseif($pointer == 'past')
            {
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start(getdate($this->now[0] + RepeaterDay::DAY_SECONDS));
                $sunday_repeater->next('past');
                $last_sunday_span = $sunday_repeater->next('past');
                $begin = $last_sunday_span->begin();
                $this->_current_week_start = $begin[0];
            }
        }
        else
        {
            $direction = $pointer == 'future' ? 1 : -1;
            $this->_current_week_start += $direction * self::WEEK_SECONDS;
        }

        return new \Chronic\Span(getdate($this->_current_week_start), getdate($this->_current_week_start + self::WEEK_SECONDS));
    }

    public function this($pointer = 'future')
    {
        switch($pointer)
        {
            case 'future':
                $this_week_start = getdate(mktime($this->now['hours'],0,0,$this->now['mon'],$this->now['mday'],$this->now['year']) + RepeaterHour::HOUR_SECONDS);
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $this_sunday_span = $sunday_repeater->this('future');
                $this_week_end = $this_sunday_span->begin();
                return new \Chronic\Span($this_week_start, $this_week_end);
            case 'past':
                $this_week_end = getdate(mktime($this->now['hours'],0,0,$this->now['mon'],$this->now['mday'],$this->now['year']));
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $last_sunday_span = $sunday_repeater->next('past');
                $this_week_start = $last_sunday_span->begin();
                return new \Chronic\Span($this_week_start, $this_week_end);
            case 'none':
                $sunday_repeater = new RepeaterDayName('sunday');
                $sunday_repeater->start($this->now);
                $last_sunday_span = $sunday_repeater->next('past');
                $this_week_start = $last_sunday_span->begin();
                return new \Chronic\Span($this_week_start, getdate($this_week_start[0] + self::WEEK_SECONDS));
        }
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        return $span->add($direction * $amount * self::WEEK_SECONDS);
    }

    public function width()
    {
        return self::WEEK_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-week';
    }
}

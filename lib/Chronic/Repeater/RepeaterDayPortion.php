<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterDayPortion extends \Chronic\Repeater
{
    public static $PORTIONS = array(
            'am' => array('begin' => 0, 'end' => 43199),
            'pm' => array('begin' => 43200, 'end' => 86399),
            'morning' => array('begin' => 21600, 'end' => 43200), // 6am-12am
            'afternoon' => array('begin' => 46800, 'end' => 61200), // 1pm-5pm
            'evening' => array('begin' => 61200, 'end' => 72000), // 5pm-8pm
            'night' => array('begin' => 72000, 'end' => 86400) // 8pm-12pm
    );

    public $_range;
    public $_current_span;

    public function __construct($type)
    {
        parent::__construct($type);

        if (is_int($type))
        {
            $this->_range = array('begin' => $this->type * 60 * 60, 'end' => ($this->type + 12) * 60 * 60);
        }
        else
        {
            if(isset(self::$PORTIONS[$type]))
            {
                $this->_range = self::$PORTIONS[$type];
            }
            else
            {
                throw new \Exception("Invalid type $type for RepeaterDayPortion");
            }
        }

        if( ! $this->_range )
            throw new \Exception("Range should have been set by now");
    }

    public function next($pointer)
    {
        $full_day = 60 * 60 * 24;

        if( ! $this->_current_span)
        {
            $now_ymd = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
            $now_seconds = $this->now[0] - $now_ymd[0];

            if($now_seconds < $this->_range['begin'])
            {
                if($pointer === 'future')
                    $range_start = $now_ymd[0] + $this->_range['begin'];
                elseif($pointer === 'past')
                    $range_start = $now_ymd[0] - $full_day + $this->_range['begin'];
            }
            elseif($now_seconds > $this->_range['end'])
            {
                if($pointer === 'future')
                    $range_start = $now_ymd[0] + $full_day + $this->_range['begin'];
                elseif($pointer === 'past')
                    $range_start = $now_ymd[0] + $this->_range['begin'];
            }
            else
            {
                if($pointer === 'future')
                    $range_start = $now_ymd[0] + $full_day + $this->_range['begin'];
                elseif($pointer === 'past')
                    $range_start = $now_ymd[0] - $full_day + $this->_range['begin'];
            }

            $this->_current_span = new  \Chronic\Span(getdate($range_start), getdate($range_start + ($this->_range['end'] - $this->_range['begin'])));
        }
        else
        {
            if($pointer === 'future')
                $this->_current_span = $this->_current_span->add($full_day);

            elseif($pointer === 'past')
                $this->_current_span = $this->_current_span->subtract($full_day);
        }

        return $this->_current_span;
    }

    public function this($context = 'future')
    {
        $now_ymd = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
        $range_start = $now_ymd[0] + $this->_range['begin'];
        $this->_current_span = new  \Chronic\Span(getdate($range_start), getdate($range_start + ($this->_range['end'] - $this->_range['begin'])));

        return $this->_current_span;
    }

    public function offset($span, $amount, $pointer)
    {
        $this->now = $span->begin();
        $portion_span = $this->next($pointer);
        $direction = $pointer == 'future' ? 1 : -1;
        return $portion_span->add($direction * ($amount - 1) * \Chronic\Repeater\RepeaterDay::DAY_SECONDS);
    }

    public function width()
    {
        if( ! isset($this->_range))
            throw new \Exception('Range has not been set');

        if(isset($this->_current_span))
            return $this->_current_span->width();

        if(is_int($this->type))
            return 12 * 60 * 60;
        else
            return $this->_range['end'] - $this->_range['begin'];
    }

    public function __toString()
    {
        parent::__toString().'-dayportion-'.$this->type;
    }
}

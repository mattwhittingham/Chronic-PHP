<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterDay extends \Chronic\Repeater
{
    const DAY_SECONDS = 86400; // 24 * 60 * 60

    private $_current_day_start;

    public function next($pointer)
    {
        if ( ! $this->_current_day_start)
        {
            $this->_current_day_start = mktime(0, 0, 0, $this->now['mon'], $this->now['mday'], $this->now['year']);
        }

        $direction = $pointer == 'future' ? 1 : -1;
        $this->_current_day_start += $direction * self::DAY_SECONDS;

        return new \Chronic\Span(getdate($this->_current_day_start), getdate($this->_current_day_start + self::DAY_SECONDS));
    }

    public function this($pointer = 'future')
    {
        switch($pointer)
        {
            case 'future':
                $day_begin = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours']);
                $day_end_tmp = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']) + self::DAY_SECONDS;
                $day_end = getdate($day_end_tmp[0] + self::DAY_SECONDS);
                break;
            case 'past':
                $day_begin = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
                $day_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours']);
                break;
            case 'none':
                $day_begin = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
                $day_end_tmp = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']) + self::DAY_SECONDS;
                $day_end = getdate($day_end_tmp[0] + self::DAY_SECONDS);
                break;
        }

        return new \Chronic\Span($day_begin, $day_end);
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        return $span->add($direction * $amount * self::DAY_SECONDS);
    }

    public function width()
    {
        return self::DAY_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-day';
    }
}

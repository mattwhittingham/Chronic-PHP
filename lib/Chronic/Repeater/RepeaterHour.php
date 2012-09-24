<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterHour extends \Chronic\Repeater
{
    const HOUR_SECONDS = 3600; // 14 * 24 * 60 * 60

    public $_current_hour_start;

    public function next($pointer)
    {
        if ( ! $this->_current_hour_start)
        {
            if($pointer == 'future')
            {
                $date =  \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'] + 1);
                $this->_current_hour_start = $date[0];
            }
            elseif($pointer == 'past')
            {
                $date = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'] - 1);
                $this->_current_hour_start = $date[0];
            }
        }
        else
        {
            $direction = $pointer == 'future' ? 1 : -1;
            $this->_current_hour_start += $direction * self::HOUR_SECONDS;
        }

        return new \Chronic\Span(getdate($this->_current_hour_start), getdate($this->_current_hour_start + self::HOUR_SECONDS));
    }

    public function this($pointer = 'future')
    {
        switch($pointer)
        {
            case 'future':
                $hour_start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] + 1);
                $hour_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'] + 1);
                break;
            case 'past':
                $hour_start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours']);
                $hour_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes']);
                break;
            case 'none':
                $hour_start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours']);
                $hour_end = getdate($hour_start[0] + self::HOUR_SECONDS);
                break;
        }

        return new \Chronic\Span($hour_start, $hour_end);
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        return $span->add($direction * $amount * self::HOUR_SECONDS);
    }

    public function width()
    {
        return self::HOUR_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-hour';
    }
}

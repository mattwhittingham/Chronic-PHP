<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterMinute extends \Chronic\Repeater
{
    const MINUTE_SECONDS = 60;

    public $_current_minute_start;

    public function next($pointer)
    {
        if ( ! $this->_current_minute_start)
        {
            if($pointer == 'future')
            {
                $date =  \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] + 1);
                $this->_current_minute_start = $date[0];
            }
            elseif($pointer == 'past')
            {
                $date =  \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] - 1);
                $this->_current_minute_start = $date[0];
            }
        }
        else
        {
            $direction = $pointer == 'future' ? 1 : -1;
            $this->_current_minute_start += $direction * self::MINUTE_SECONDS;
        }

        return new \Chronic\Span(getdate($this->_current_minute_start), getdate($this->_current_minute_start + self::MINUTE_SECONDS));
    }

    public function this($pointer = 'future')
    {
        switch($pointer)
        {
            case 'future':
                $minute_begin = $this->now;
                $minute_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] + 1);
                break;
            case 'past':
                $minute_begin = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] + 1);
                $minute_end = $this->now;
                break;
            case 'none':
                $minute_begin = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] + 1);
                $date = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'], $this->now['hours'], $this->now['minutes'] + 1);
                $minute_end = getdate($date[0] + self::MINUTE_SECONDS);
                break;
        }

        return new \Chronic\Span($minute_begin, $minute_end);
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        return $span->add($direction * $amount * self::MINUTE_SECONDS);
    }

    public function width()
    {
        return self::MINUTE_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-minute';
    }
}

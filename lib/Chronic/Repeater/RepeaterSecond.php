<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterSecond extends \Chronic\Repeater
{
    const SECOND_SECONDS = 1;
    public $_seconds_start;

    public function next($pointer = 'future')
    {
        $direction = $pointer == 'future' ? 1 : -1;

        if( ! $this->_seconds_start)
        {
            $this->_seconds_start = $this->now[0] + ($direction * self::SECOND_SECONDS);
        }
        else
        {
            $this->_seconds_start += self::SECOND_SECONDS * $direction;
        }

        return new \Chronic\Span(getdate($this->_seconds_start), getdate($this->_seconds_start + self::SECOND_SECONDS));
    }

    public function this($pointer = 'future')
    {
        return new \Chronic\Span($this->now, getdate($this->now[0] + 1));
    }

    public function offset($span, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;
        return $span->add($direction * $amount * self::SECOND_SECONDS);
    }

    public function width()
    {
        return self::SECOND_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-second';
    }
}

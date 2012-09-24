<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater\RepeaterTime;

class Tick
{
    private  $_time;
    private $_ambiguous;

    function __construct($time, $ambiguous = false)
    {
        $this->_time = $time;
        $this->_ambiguous = $ambiguous;
    }

    function ambiguous()
    {
        return $this->_ambiguous;
    }

    function multiply($other)
    {
        return new Tick($this->_time * $other, $this->_ambiguous);
    }

    function to_f()
    {
        return (float)$this->_time;
    }

    function __toString()
    {
        return $this->_time.($this->_ambiguous ? '?' : '');
    }

    public function setTime($time)
    {
        $this->_time = $time;
    }

    public function getTime()
    {
        return $this->_time;
    }
}


<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class Span
{
    private $begin;
    private $end;

    public function __construct($begin, $end)
    {
        $this->begin = $begin;
        $this->end = $end;
    }

    public function width()
    {
        return $this->end[0] - $this->begin[0];
    }

    public function add($seconds)
    {
        return new Span(getdate($this->begin[0] + $seconds), getdate($this->end[0] + $seconds));
    }

    public function subtract($seconds)
    {
        return $this->add(-$seconds);
    }

    public function __toString()
    {
        return '('.$this->begin[0].'..'.$this->end[0].')';
    }

    public function begin()
    {
        return $this->begin;
    }

    public function end()
    {
        return $this->end;
    }

    public function cover($val)
    {
        return $this->begin <= $val && $this->end >= $val;
    }
}

<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class MiniDate
{
    private $_month;
    private $_day;

    public static function fromTime($time)
    {
        return new MiniDate($time['mon'], $time['mday']);
    }

    public function __construct($month, $day)
    {
        if( ! ($month >= 1 && $month <= 12))
            throw new \InvalidArgumentException('1..12 are valid months');

        $this->_month = $month;
        $this->_day = $day;
    }

    public function isBetween(MiniDate $md_start, MiniDate $md_end)
    {
        if(($this->_month === $md_start->getMonth() && $this->_month === $md_end->getMonth()) &&
        ($this->_day < $md_start->getDay() || $this->_day > $md_end->getDay()))
            return false;

        if(($this->_month === $md_start->getMonth() && $this->_day >= $md_start->getDay()) ||
            ($this->_month === $md_end->getMonth() && $this->_day <= $md_end->getDay()))
            return true;

        $i = ($md_start->getMonth() % 12) + 1;

        while($i !== $md_end->getMonth())
        {
            if($this->_month === $i)
                return true;
            $i = ($i % 12) + 1;
        }

        return false;
    }

    public function equals(MiniDate $other)
    {
        return $this->_month === $other->getMonth() && $this->_day === $other->getDay();
    }

    public function getMonth()
    {
        return $this->_month;
    }

    public function setMonth($month)
    {
        $this->_month = $month;
    }

    public function getDay()
    {
        return $this->_day;
    }

    public function setDay($day)
    {
        $this->_day = $day;
    }
}

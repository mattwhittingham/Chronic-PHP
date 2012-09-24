<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterMonth extends \Chronic\Repeater
{
    const MONTH_SECONDS = 2592000; // 30 * 24 * 60 * 60
    const YEAR_MONTHS = 12;
    public static $MONTH_DAYS = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    public static $MONTH_DAYS_LEAP = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    public $current_month_start;

    public function next($pointer)
    {
        if ( ! $this->current_month_start)
            $this->current_month_start = $this->offset_by(\Chronic::construct($this->now['year'], $this->now['mon']), 1, $pointer);
        else
            $this->current_month_start = $this->offset_by(\Chronic::construct($this->current_month_start['year'], $this->current_month_start['mon']), 1,$pointer);

        $month_end = \Chronic::construct($this->current_month_start['year'], $this->current_month_start['mon'] + 1);

        return new \Chronic\Span($this->current_month_start, $month_end);
    }

    public function this($pointer = 'future')
    {
        switch($pointer)
        {
            case 'future':
                $month_start = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday'] + 1);
                $month_end = $this->offset_by(\Chronic::construct($this->now['year'], $this->now['mon']), 1, 'future');
                break;
            case 'past':
                $month_start = \Chronic::construct($this->now['year'], $this->now['mon']);
                $month_end = \Chronic::construct($this->now['year'], $this->now['mon'], $this->now['mday']);
                break;
            case 'none':
                $month_start = \Chronic::construct($this->now['year'], $this->now['mon']);
                $month_end = $this->offset_by(\Chronic::construct($this->now['year'], $this->now['mon']), 1, 'future');
                break;
        }

        return new \Chronic\Span($month_start, $month_end);
    }

    public function offset($span, $amount, $pointer)
    {
        $begin = $this->offset_by($span->begin(), $amount, $pointer);
        $end =  $this->offset_by($span->end(), $amount, $pointer);
        return new \Chronic\Span($begin, $end);
    }

    public function offset_by($time, $amount, $pointer)
    {
        $direction = $pointer == 'future' ? 1 : -1;

        $amount *= $direction;

        $amount_years =  floor($amount / self::YEAR_MONTHS);

        // modulus works different than ruby
        $amount_months =  $amount - floor($amount / self::YEAR_MONTHS) * self::YEAR_MONTHS;

        $new_year = $time['year'] + $amount_years;
        $new_month = $time['mon'] + $amount_months + ($amount_months < 0 ? self::YEAR_MONTHS : 0);

        if ($new_month > self::YEAR_MONTHS)
        {
            $new_year++;
            $new_month -= self::YEAR_MONTHS;
        }

        $days = $this->month_days($new_year, $new_month);
        $new_day = $time['mday'] > $days ? $days : $time['mday'];

        return \Chronic::construct($new_year, $new_month, $new_day, $time['hours'], $time['minutes'], $time['seconds']);
    }

    public function width()
    {
        return self::MONTH_SECONDS;
    }

    public function __toString()
    {
        return parent::__toString().'-month';
    }

    private function month_days($year, $month)
    {
        return date('L',mktime(0, 0, 0, 0, 0,$year)) ? self::$MONTH_DAYS_LEAP[$month - 1] : self::$MONTH_DAYS[$month - 1];
    }
}

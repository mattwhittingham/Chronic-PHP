<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterMonthName extends \Chronic\Repeater
{
    const MONTH_SECONDS = 2592000;
    public static $MONTHS = array(
        'january' => 1,
        'february' => 2,
        'march' => 3,
        'april' => 4,
        'may' => 5,
        'june' => 6,
        'july' => 7,
        'august' => 8,
        'september' => 9,
        'october' => 10,
        'november' => 11,
        'december' => 12
    );
    public $_current_month_begin;
    public $_index;

    public function next($pointer)
    {
        if(! $this->_current_month_begin)
        {
            switch($pointer)
            {
                case 'future':
                    if ($this->now['mon'] < $this->index())
                        $this->_current_month_begin = \Chronic::construct($this->now['year'], $this->index());
                    elseif ($this->now['mon'] > $this->index())
                        $this->_current_month_begin = \Chronic::construct($this->now['year'] + 1, $this->index());
                    break;
                case 'none':
                    if ($this->now['mon'] < $this->index())
                        $this->_current_month_begin = \Chronic::construct($this->now['year'], $this->index());
                    elseif ($this->now['mon'] > $this->index())
                        $this->_current_month_begin = \Chronic::construct($this->now['year'] + 1, $this->index());
                    break;
                case 'past':
                    if ($this->now['mon'] >= $this->index())
                        $this->_current_month_begin = \Chronic::construct($this->now['year'], $this->index());
                    elseif ($this->now['mon'] < $this->index())
                        $this->_current_month_begin = \Chronic::construct($this->now['year'] - 1, $this->index());
                    break;
            }

            if( ! isset($this->_current_month_begin))
                throw new \Exception('Current month should be set by now');
        }
        else
        {
            switch ($pointer)
            {
                case 'future':
                    $this->_current_month_begin = \Chronic::construct($this->_current_month_begin['year'] + 1, $this->_current_month_begin['mon']);
                    break;
                case 'past':
                    $this->_current_month_begin = \Chronic::construct($this->_current_month_begin['year'] - 1, $this->_current_month_begin['mon']);
                    break;
            }
        }

        $cur_month_year = $this->_current_month_begin['year'];
        $cur_month_month = $this->_current_month_begin['mon'];

        if ($cur_month_month == 12)
        {
            $next_month_year = $cur_month_year + 1;
            $next_month_month = 1;
        }
        else
        {
            $next_month_year = $cur_month_year;
            $next_month_month = $cur_month_month + 1;
        }

        $next_month = \Chronic::construct($next_month_year, $next_month_month);

        return new \Chronic\Span($this->_current_month_begin, $next_month);
    }

    public function this($pointer = 'future')
    {
        switch($pointer)
        {
            case 'past':
                return $this->next($pointer);
            case 'future':
                return $this->next('none');
            case 'none':
                return $this->next('none');
        }
    }

    public function width()
    {
        return self::MONTH_SECONDS;
    }

    public function index()
    {
        return $this->_index = $this->_index ?: self::$MONTHS[$this->type];
    }

    public function __toString()
    {
        return parent::__toString().'-monthname-'.$this->type;
    }
}

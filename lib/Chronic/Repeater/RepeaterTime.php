<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterTime extends \Chronic\Repeater
{
    public $_current_time;

    function __construct($time)
    {
        $t = preg_replace('/\:/', '', $time);

        switch(strlen($t))
        {
            case 1:
            case 2:
                $hours = (int)$t;
                $this->type = new RepeaterTime\Tick(($hours === 12 ? 0 : $hours) * 60 * 60, true);
                break;
            case 3:
                $hours = (int)substr($t, 0, 1);
                $ambiguous = $hours > 0;
                $this->type = new RepeaterTime\Tick(($hours * 60 * 60) + ((int)substr($t, 1, 2) * 60), $ambiguous);
                break;
            case 4:
                $ambiguous = preg_match('/:/', $time) && (int)substr($t, 0, 1) !== 0 && (int)substr($t, 0, 2) <= 12;
                $hours = (int)substr($t, 0, 2);
                $this->type = $hours === 12
                    ? new RepeaterTime\Tick(0 * 60 * 60 + (int)substr($t, 2, 2) * 60, $ambiguous)
                    : new RepeaterTime\Tick($hours * 60 * 60 + (int)substr($t, 2, 2) * 60, $ambiguous);
                break;
            case 5:
                $this->type = new RepeaterTime\Tick((int)substr($t, 0, 1) * 60 * 60 + (int)substr($t, 1, 2) * 60 + (int)substr($t, 3, 2), true);
                break;
            case 6:
                $ambiguous = preg_match('/:/', $time) && (int)substr($t, 0, 1) != 0 && (int)substr($t, 0, 2) <= 12;
                $hours = (int)substr($t, 0, 2);
                $this->type = $hours === 12
                    ? new RepeaterTime\Tick(0 * 60 * 60 + (int)substr($t, 2, 2) * 60 + (int)substr($t, 4, 2), $ambiguous)
                    : new RepeaterTime\Tick($hours * 60 * 60 + (int)substr($t, 2, 2) * 60 + (int)substr($t, 4, 2), $ambiguous);
                break;
            default:
                throw new \Exception('Time cannot exceed six digits');
        }
    }

    function next($pointer)
    {
        $half_day = 60 * 60 * 12;
        $full_day = 60 * 60 * 24;

        $first = false;

        if( ! $this->_current_time)
        {
            $first = true;
            $midnight = mktime(0,0,0,$this->now['mon'],$this->now['mday'],$this->now['year']);

            $yesterday_midnight = $midnight - $full_day;
            $tomorrow_midnight = $midnight + $full_day;

            $offset_fix = (int)date('Z', $midnight) - (int)date('Z', $tomorrow_midnight);
            $tomorrow_midnight += $offset_fix;

            if ($pointer == 'future')
            {
                if($this->type->ambiguous())
                {
                    foreach (array($midnight + $this->type->getTime() + $offset_fix, $midnight + $half_day + $this->type->getTime() + $offset_fix, $tomorrow_midnight + $this->type->getTime() + $half_day) as $t)
                    {
                        if($t >= $this->now[0])
                        {
                            $this->_current_time = $t;
                            break;
                        }
                    }
                }
                else
                {
                    foreach(array($midnight + $this->type->getTime() + $offset_fix, $tomorrow_midnight + $this->type->getTime()) as $t)
                    {
                        if($t >= $this->now[0])
                        {
                            $this->_current_time = $t;
                            break;
                        }
                    }
                }
            }
            else
            {
                if($this->type->ambiguous())
                {
                   foreach(array($midnight + $half_day + $this->type->getTime() + $offset_fix, $midnight + $this->type->getTime() + $offset_fix, $yesterday_midnight + $this->type->getTime() + $half_day) as $t)
                   {
                       if($t <= $this->now[0])
                       {
                           $this->_current_time = $t;
                           break;
                       }
                   }
                }
                else
                {
                    foreach(array($midnight + $this->type->getTime() + $offset_fix, $yesterday_midnight + $this->type->getTime()) as $t)
                    {
                        if($t <= $this->now[0])
                        {
                            $this->_current_time = $t;
                            break;
                        }
                    }
                }
            }
        }

        if ( ! $this->_current_time)
            throw new \Exception('Current time cannot be nil at this point');

        if( ! $first)
        {
            $increment = $this->type->ambiguous() ? $half_day : $full_day;
            $this->_current_time += $pointer == 'future' ? $increment : -$increment;
        }

        return new \Chronic\Span(getdate($this->_current_time), getdate($this->_current_time + $this->width()));
    }

    function this($context = 'future')
    {
        if($context == 'none')
            $context = 'future';

        return $this->next($context);
    }

    function width()
    {
        return 1;
    }

    function __toString()
    {
       return parent::__toString.'-time-'.$this->type;
    }
}



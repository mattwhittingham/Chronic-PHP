<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Repeater;

class RepeaterDayName extends \Chronic\Repeater
{
    const DAY_SECONDS = 86400; // 24 * 60 * 60
    public $_current_date;

    public function next($pointer)
    {
        $direction = $pointer === 'future' ? 1 : -1;

        if ( ! isset($this->_current_date))
        {
            $this->_current_date = new \DateTime('@'.mktime(0,0,0,$this->now['mon'], $this->now['mday'], $this->now['year']));

            if($direction > 0)
                $this->_current_date->add(new \DateInterval('P1D'));
            else
                $this->_current_date->sub(new \DateInterval('P1D'));

            $day_num = $this->symbol_to_number($this->type);

            while($this->_current_date->format('w') != $day_num)
                if($direction > 0)
                    $this->_current_date->add(new \DateInterval('P1D'));
                else
                    $this->_current_date->sub(new \DateInterval('P1D'));

        }
        else
        {
            if($direction > 0)
                $this->_current_date->add(new \DateInterval('P7D'));
            else
                $this->_current_date->sub(new \DateInterval('P7D'));
        }

        $next_date = clone $this->_current_date;
        $next_date->add(new \DateInterval('P1D'));

        return new \Chronic\Span(
            \Chronic::construct($this->_current_date->format('Y'), $this->_current_date->format('n'), $this->_current_date->format('j')),
            \Chronic::construct($next_date->format('Y'), $next_date->format('n'), $next_date->format('j'))
        );
    }

    public function this($pointer = 'future')
    {
        if($pointer == 'none')
            $pointer = 'future';
        return $this->next($pointer);
    }

    public function width()
    {
        return self::DAY_SECONDS;
    }

    public function __toString()
    {
        parent::__toString().'-dayname-'.$this->type;
    }

    private function symbol_to_number($sym)
    {
        $lookup = array('sunday' =>  0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6);

        if( ! isset($lookup[$sym]))
            throw new \Exception("Invalid symbol specified");

        return $lookup[$sym];
    }
}

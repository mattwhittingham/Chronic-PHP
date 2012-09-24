<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

use Chronic,
Chronic\Grabber,
Chronic\Span,
Chronic\Token,
Chronic\Repeater\RepeaterMonth,
Chronic\Repeater\RepeaterDayPortion;
use OutOfBoundsException;

class Handlers
{
    static function handleMonthDay($month, $day, $time_tokens, $options)
    {
        $month->start(Chronic::getNow());
        $span = $month->this($options['context']);
        $begin = $span->begin();

        list($year, $month) = array($begin['year'], $begin['mon']);
        $day_start = getdate(mktime(0, 0, 0, $month, $day, $year));

        return self::dayOrTime($day_start, $time_tokens, $options);
    }

    static function handleRepeaterMonthNameScalarDay($tokens, $options)
    {
        $month = $tokens[0]->get_tag('RepeaterMonthName');
        $day = $tokens[1]->get_tag('ScalarDay')->getType();

        $now = Chronic::getNow();

        if (self::monthOverflow($now['year'], $month->index(), $day))
            return null;

        return self::handleMonthDay($month, $day, array_slice($tokens, 2), $options);
    }

    static function monthOverflow($year, $month, $day)
    {
        try
        {
            if (date('L',mktime(0, 0, 0, 0, 0,$year)))
                return $day > RepeaterMonth::$MONTH_DAYS_LEAP[$month - 1];
            else
                return $day > RepeaterMonth::$MONTH_DAYS[$month - 1];
        }
        catch(OutOfBoundsException $e)
        {
            return false;
        }
    }

    static function dayOrTime($day_start, $time_tokens, $options)
    {
        $outer_span = new Span($day_start, getdate($day_start[0] + (24 * 60 * 60)));

        if( ! empty($time_tokens))
        {
            Chronic::setNow($outer_span->begin());
            return self::getAnchor(self::dealiasAndDisambiguateTimes($time_tokens, $options), $options);
        }
        else
        {
            return $outer_span;
        }
    }

    static function getAnchor($tokens, $options)
    {
        $grabber = new Grabber('this');
        $pointer = 'future';

        $repeaters = self::getRepeaters($tokens);

        for($i = 0, $len = sizeof($repeaters); $i < $len; $i++)
            array_pop($tokens);

        if(isset($tokens[0]) && $tokens[0]->getTag('Grabber'))
        {
            $grabber =  array_shift($tokens);
            $grabber = $grabber->getTag('Grabber');
        }

        $head = array_shift($repeaters);
        $head->start = Chronic::getNow();

        switch($grabber->getType())
        {
            case 'last':
                $outer_span = $head->next('past');
                break;
            case 'this':
                if ($options['context'] != 'past' && sizeof($repeaters) > 0)
                    $outer_span = $head->this('none');
                else
                    $outer_span = $head->this($options['context']);
                break;
            case 'next':
                $outer_span = $head->next('future');
                break;
            default:
                throw new \Exception('Invalid grabber');
        }

        return self::findWithin($repeaters, $outer_span, $pointer);

    }

    static function getRepeaters($tokens)
    {
        $array = array_map(function($token){ return $token->getTag('Repeater');}, $tokens);
        $array = array_filter($array, 'isset');
        sort($array);
        $array = array_reverse($array);
        return $array;
    }

# Recursively finds repeaters within other repeaters.
# Returns a Span representing the innermost time span
# or nil if no repeater union could be found
    static function findWithin($tags, Span $span, $pointer)
    {
        if (empty($tags)) return $span;

        $head = array_shift($tags);
        $head->start($pointer == 'future' ? $span->begin() : $span->end());
        $h = $head->this('none');

        if ($span->cover($h->begin()) || $span->cover($h->end()))
        {
            return self::findWithin($tags, $h, $pointer);
        }

        return null;
    }

    static function dealiasAndDisambiguateTimes($tokens, $options)
    {
        // handle aliases of am/pm
        // 5:00 in the morning -> 5:00 am
        // 7:00 in the evening -> 7:00 pm

        $day_portion_index = null;
        for($i=0, $len = sizeof($tokens); $i < $len; $i++ )
        {
            if($tokens[$i]->getTag('RepeaterDayPortion'))
            {
                $day_portion_index = $i;
                break;
            }
        }

        $time_index = null;
        for($i = 0, $len = sizeof($tokens); $i < $len; $i++ )
        {
            if($tokens[$i]->getTag('RepeaterTime'))
            {
                $time_index = $i;
                break;
            }
        }

        if($day_portion_index && $time_index)
        {
            $t1 = $tokens[$day_portion_index];
            $t1tag = $t1->getTag('RepeaterDayPortion');

            switch($t1tag->getType())
            {
                case 'morning':
                    $t1->untag('RepeaterDayPortion');
                    $t1->tag(new RepeaterDayPortion('am'));
                    break;
                case 'afternoon':
                case 'evening':
                case 'night':
                    $t1->untag('RepeaterDayPortion');
                    $t1->tag(new RepeaterDayPortion('pm'));
                    break;
            }

            // handle ambiguous times if 'ambigous_time_range is specified
            if ($options['ambiguous_time_range'] != 'none')
            {
                $ambiguous_tokens = array();
                for($i = 0, $len = sizeof($tokens); $i < $len; $i++)
                {
                    $ambiguous_tokens[] = $tokens[$i];
                    $next_token = $tokens[$i + 1];

                    if($tokens[$i]->getTag('RepeaterTime') && $tokens[$i]->getTag('RepeaterTime')->getType()->ambiguous() && ( ! $next_token || ! $next_token->getTag('RepeaterDayPortion')))
                    {
                        $distoken = new Token('disambiguator');

                        $distoken->tag(new RepeaterDayPortion($options['ambiguous_time_range']));
                        $ambiguous_tokens[] = $distoken;
                    }

                    $tokens = $ambiguous_tokens;
                }
            }

            return $tokens;
        }
    }
}


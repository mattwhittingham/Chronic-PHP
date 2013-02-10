<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

use Chronic\Token,
    Chronic\Grabber,
    Chronic\Separator,
    Chronic\Repeater,
    Chronic\Pointer,
    Chronic\Handler;

class Chronic {
    private static $DEFAULT_OPTIONS = array(
        'context' => 'future',
        'now' => null,
        'guess' => true,
        'ambiguous_time_range' => 6,
        'endian_precedence' => array('middle', 'little'),
        'ambiguous_year_future_bias' => 50
    );

    private static $timeClass = 'DateTime';
    private static $now;
    private static $definitions;

    public static function parse($text, $opts = array())
    {
        $options = array_merge(self::$DEFAULT_OPTIONS, $opts);

        foreach(array_diff_key($opts, self::$DEFAULT_OPTIONS) as $key)
        {
            throw new \InvalidArgumentException("$key is not a valid option key");
        }

        if( ! in_array($options['context'], array('past', 'future', 'none')))
        {
            throw new \InvalidArgumentException('Invalid context, past/future only');
        }

        $options['text'] = $text;
        self::$now = getdate($options['now'] ?: time());

        // tokenize words
        $tokens = self::tokenize($text, $options);

        $span = self::tokensToSpan($tokens, $options);

        return $options['guess'] ? self::guess($span) : $span;
    }

    public static function preNormalize($text)
    {
        $text = strtolower($text);

        $text = preg_replace('/\./', ':', $text);
        $text = preg_replace('/[\'"]/', '', $text);
        $text = preg_replace('/,/', ' ', $text);
        $text = preg_replace('/\bsecond (of|day|month|hour|minute|second)\b/', '2nd \1', $text);
        $text = \Chronic\Numerizer::numerize($text);
        $text = preg_replace('/ \-(\d{4})\b/', ' tzminus\1', $text);
        $text = preg_replace('/([\/\-\,\@])/', ' $1 ', $text);
        $text = preg_replace('/(?:^|\s)0(\d+:\d+\s*pm?\b)/', '\1', $text);
        $text = preg_replace('/\btoday\b/', 'this day', $text);
        $text = preg_replace('/\btomm?orr?ow\b/', 'next day', $text);
        $text = preg_replace('/\byesterday\b/', 'last day', $text);
        $text = preg_replace('/\bnoon\b/', '12:00pm', $text);
        $text = preg_replace('/\bmidnight\b/', '24:00', $text);
        $text = preg_replace('/\bnow\b/', 'this second', $text);
        $text = preg_replace('/\b(?:ago|before(?: now)?)\b/', 'past', $text);
        $text = preg_replace('/\bthis (?:last|past)\b/', 'last', $text);
        $text = preg_replace('/\b(?:in|during) the (morning)\b/', '\1', $text);
        $text = preg_replace('/\b(?:in the|during the|at) (afternoon|evening|night)\b/', '\1', $text);
        $text = preg_replace('/\btonight\b/', 'this night', $text);
        $text = preg_replace('/\b\d+:?\d*[ap]\b/','\0m', $text);
        $text = preg_replace('/(\d)([ap]m|oclock)\b/', '\1 \2', $text);
        $text = preg_replace('/\b(hence|after|from)\b/', 'future', $text);

        return $text;
    }

    public static function guess(Span $span)
    {
        if($span->width() > 1)
           return $span->begin() + ($span->width() / 2);

        return $span->begin();
    }


    public static function definitions($options = array())
    {
        $options['endian_precedence'] = isset($options['endian_precedence']) ? $options['endian_precedence'] : array('middle', 'little');

        self::$definitions = self::$definitions  ?: array(
            'time' => array(
                new Handler(array(':repeater_time', ':repeater_day_portion'), null)
            ),
            'date' => array(
                new Handler(array(':repeater_day_name', ':repeater_month_name', ':scalar_day', ':repeater_time', ':separator_slash_or_dash', ':time_zone', ':scalar_year'), ':handle_rdn_rmn_sd_t_tz_sy'),
                new Handler(array(':repeater_day_name', ':repeater_month_name', ':scalar_day'), ':handle_rdn_rmn_sd'),
                new Handler(array(':repeater_day_name', ':repeater_month_name', ':scalar_day', ':scalar_year'), ':handle_rdn_rmn_sd_sy'),
                new Handler(array(':repeater_day_name', ':repeater_month_name', ':ordinal_day'), ':handle_rdn_rmn_od'),
                new Handler(array(':scalar_year', ':separator_slash_or_dash', ':scalar_month', ':separator_slash_or_dash', ':scalar_day', ':repeater_time', ':time_zone'), ':handle_sy_sm_sd_t_tz'),
                new Handler(array(':repeater_month_name', ':scalar_day', ':scalar_year'), ':handle_rmn_sd_sy'),
                new Handler(array(':repeater_month_name', ':ordinal_day', ':scalar_year'), ':handle_rmn_od_sy'),
                new Handler(array(':repeater_month_name', ':scalar_day', ':scalar_year', ':separator_at', 'time?'), ':handle_rmn_sd_sy'),
                new Handler(array(':repeater_month_name', ':ordinal_day', ':scalar_year', ':separator_at', 'time?'), ':handle_rmn_od_sy'),
                new Handler(array(':repeater_month_name', ':scalar_day', ':separator_at', 'time?'), ':handleRepeaterMonthNameScalarDay'),
                new Handler(array(':repeater_time', ':repeater_day_portion', ':separator_on', ':repeater_month_name', ':scalar_day'), ':handle_rmn_sd_on'),
                new Handler(array(':repeater_month_name', ':ordinal_day', ':separator_at', ':time'), ':handle_rmn_od'),
                new Handler(array(':ordinal_day', ':repeater_month_name', ':scalar_year', ':separator_at', 'time?'), ':handle_od_rmn_sy'),
                new Handler(array(':ordinal_day', ':repeater_month_name', ':separator_at', 'time?'), ':handle_od_rmn'),
                new Handler(array(':scalar_year', ':repeater_month_name', ':ordinal_day'), ':handle_sy_rmn_od'),
                new Handler(array(':repeater_time', ':repeater_day_portion', ':separator_on', ':repeater_month_name', ':ordinal_day'), ':handle_rmn_od_on'),
                new Handler(array(':repeater_month_name', ':scalar_year'), ':handle_rmn_sy'),
                new Handler(array(':scalar_day', ':repeater_month_name', ':scalar_year', ':separator_at', 'time?'), ':handle_sd_rmn_sy'),
                new Handler(array(':scalar_day', ':repeater_month_name', ':separator_at', 'time?'), ':handle_sd_rmn'),
                new Handler(array(':scalar_year', ':separator_slash_or_dash', ':scalar_month', ':separator_slash_or_dash', ':scalar_day', ':separator_at', 'time?'), ':handle_sy_sm_sd'),
                new Handler(array(':scalar_month', ':separator_slash_or_dash', ':scalar_day'), ':handle_sm_sd'),
                new Handler(array(':scalar_month', ':separator_slash_or_dash', ':scalar_year'), ':handle_sm_sy')
            ),
            // tonight at 7pm
            'anchor' => array(
                new Handler(array(':grabber', ':repeater', ':separator_at', ':repeater', ':repeater'), ':handle_r'),
                new Handler(array(':grabber', ':repeater', ':repeater', ':separator_at', ':repeater', ':repeater'), ':handle_r'),
                new Handler(array(':repeater', ':grabber', ':repeater'), ':handle_r_g_r')
            ),

            // 3 weeks from now, in 2 months
            'arrow' => array(
                new Handler(array(':scalar', ':repeater', ':pointer'), ':handle_s_r_p'),
                new Handler(array(':pointer', ':scalar', ':repeater'), ':handle_p_s_r'),
                new Handler(array(':scalar', ':repeater', ':pointer', ':anchor?'), ':handle_s_r_p_a')
            ),

            // 3rd week in march
            'narrow' => array(
                new Handler(array(':ordinal', ':repeater', ':separator_in', ':repeater'), ':handle_o_r_s_r'),
                new Handler(array(':ordinal', ':repeater', ':grabber', ':repeater'), ':handle_o_r_g_r')
            )           
        );

        $endians = array(
            new Handler(array(':scalar_month', ':separator_slash_or_dash', ':scalar_day', ':separator_slash_or_dash', ':scalar_year', ':separator_at', 'time?'), ':handle_sm_sd_sy'),
            new Handler(array(':scalar_day', ':separator_slash_or_dash', ':scalar_month', ':separator_slash_or_dash', ':scalar_year', ':separator_at', 'time?'), ':handle_sd_sm_sy')
        );

        switch ($endian = (isset($options['endian_precedence']) && isset($options['endian_precedence'][0])) ? $options['endian_precedence'][0] : null)
        {
            case 'little':
                self::$definitions['endian'] = array_reverse($endians);
                break;
            case 'middle':
                self::$definitions['endian'] = $endians;
                break;            
            default:
                throw new \InvalidArgumentException("Unknown endian option '$endian'");
        }

        return self::$definitions;
    }

    /**
     * Construct a new time object determining possible month overflows
     * and leap years. Returns a new Time object constructed from these params.
     * @static
     * @param $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     */
    public static function construct($year, $month = 1, $day = 1, $hour = 0, $minute = 0, $second = 0)
    {
        if ($second >= 60)
        {
            $minute += $second / 60;
            $second = $second % 60;
        }

        if ($minute >= 60)
        {
            $hour += $minute / 60;
            $minute = $minute % 60;
        }

        if ($hour >= 24)
        {
            $day += $hour / 24;
            $hour = $hour % 24;
        }

        // determine if there is a day overflow. this is complicated by our crappy calendar
        // system (non-constant number of days per month)
        if ($day > 56)
            throw new \InvalidArgumentException('day must be no more than 56 (makes month resolution easier)');

        if ($day > 28)
        {
            // no month ever has fewer than 28 days , so only do this if necessary
            $leap_year_month_days = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
            $common_year_month_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
            $days_this_month = date('L',mktime(0, 0, 0, 0, 0,$year)) ? $leap_year_month_days[$month - 1] : $common_year_month_days[$month - 1];

            if ($day > $days_this_month)
            {
                $month += $day / $days_this_month;
                $day = $day % $days_this_month;
            }
        }

        if ($month > 12)
        {
            if($month % 12 == 0)
            {
                $year += ($month - 12) / 12;
                $month = 12;
            }
            else
            {
                $year += $month / 12;
                $month = $month % 12;
            }
        }

        return getdate(mktime($hour, $minute, $second, $month, $day, $year));
    }

    public static function fullyQualifiedNameSpaceLookup($className)
    {
        switch ($className) {
            case 'Repeater':
            case 'Grabber': 
            case 'MiniDate': 
            case 'Scalar': 
            case 'ScalarDay': 
            case 'ScalarMonth':
            case 'ScalarYear':
            case 'Season':
                return 'Chronic\\'.$className;
            case 'RepeaterDay':
            case 'RepeaterDayName':
            case 'RepeaterDayPortion':
            case 'RepeaterFortnight':
            case 'RepeaterHour':
            case 'RepeaterMonth':
            case 'RepeaterMonthName':
            case 'RepeaterSeason':
            case 'RepeaterSeasonName':
            case 'RepeaterSecond':
            case 'RepeaterTime':
            case 'RepeaterWeek':
            case 'RepeaterYear':
                return 'Chronic\\Repeater\\'.$className;
            default: return $className;
        }
    }

    private static function tokenize($text, $options)
    {
        $text = self::preNormalize($text);

        $tokens = array_map(
            function($word){ return new Token($word); },
            explode(' ', $text)
        );

        Repeater::scan($tokens, $options);
        Grabber::scan($tokens,$options);
        Pointer::scan($tokens, $options);
        Separator::scan($tokens, $options);
        //TODO: fix this
//        foreach(array('Repeater', 'Grabber', 'Pointer', 'Scalar', 'Ordinal', 'Separator', 'TimeZone') as $tok)
//        {
//            $tok->scan($tokens, $options);
//        }
//
        return array_filter($tokens, function($token) { return $token->tagged(); });
    }

    private static function tokensToSpan($tokens, $options)
    {
        $definitions = self::definitions($options);

        foreach(($definitions['endian'] + $definitions['date']) as $handler)
        {
            if ($handler->match($tokens, $definitions))
            {
                $goodTokens = array_filter($tokens, function(Token $token){ return ! $token->getTag('Separator');});
                return $handler->invoke('date', $goodTokens, $options);
            }
        }

        foreach($definitions['anchor'] as $handler)
        {
            if ($handler->match($tokens, $definitions))
            {
                $goodTokens = array_filter($tokens, function(Token $token){ return ! $token->getTag('Separator');});
                return $handler->invoke('anchor', $goodTokens, $options);
            }
        }

        foreach($definitions['arrow'] as $handler)
        {
            if ($handler->match($tokens, $definitions))
            {
                $goodTokens = array_filter($tokens, function(Token $token){ return ! ($token->getTag('Separator') || $token->getTag('SeparatorSlashOrDash') || $token->getTag('SeparatorComma'));});
                return $handler->invoke('arrow', $goodTokens, $options);
            }
        }

       foreach($definitions['narrow'] as $handler)
       {
           if ($handler->match($tokens, $definitions))
                return $handler->invoke('narrow', $tokens, $options);
       }

        return null;
    }
}
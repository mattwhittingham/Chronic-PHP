<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class Repeater extends \Chronic\Tag
{
    # Scan an Array of Token objects and apply any necessary Repeater
    # tags to each token.
    #
    # tokens - An Array of tokens to scan.
    # options - The Hash of options specified in Chronic::parse.
    #
    # Returns an Array of tokens.
    /**
     * Scan an Array of Token objects and apply any necessary Repeater
     * tags to each token.
     * @static
     * @param $tokens - An Array of tokens to scan.
     * @param $options - The Hash of options specified in Chronic::parse.
     * @return mixed - Returns an Array of tokens
     */
    public static function scan(&$tokens, $options)
    {
        foreach($tokens as $token)
        {
            if ($t = self::scan_for_season_names($token)){ $token->tag($t); continue; }
            if ($t = self::scan_for_month_names($token)){ $token->tag($t); continue; }
            if ($t = self::scan_for_day_names($token)){ $token->tag($t); continue; }
            if ($t = self::scan_for_day_portions($token)){ $token->tag($t); continue; }
            if ($t = self::scan_for_times($token)){ $token->tag($t); continue; }
        }
    }

    public static function scan_for_season_names($token)
    {
        return self::scan_for(
            $token,
            '\Chronic\Repeater\RepeaterSeasonName',
            array(
               '/^springs?$/' => 'spring',
               '/^summers?$/' => 'summer',
               '/^(autumn)|(fall)s?$/' => 'autumn',
               '/^winters?$/' => 'winter'
            )
        );
    }

    public static function scan_for_month_names($token)
    {
        return self::scan_for($token, '\Chronic\Repeater\RepeaterMonthName', array(
            '/^jan\.?(uary)?$/' => 'january',
            '/^feb\.?(ruary)?$/' => 'february',
            '/^mar\.?(ch)?$/' => 'march',
            '/^apr\.?(il)?$/' => 'april',
            '/^may$/' => 'may',
            '/^jun\.?e?$/' => 'june',
            '/^jul\.?y?$/' => 'july',
            '/^aug\.?(ust)?$/' => 'august',
            '/^sep\.?(t\.?|tember)?$/' => 'september',
            '/^oct\.?(ober)?$/' => 'october',
            '/^nov\.?(ember)?$/' => 'november',
            '/^dec\.?(ember)?$/' => 'december'
        ));
    }

    public static function scan_for_day_names($token)
    {
        return self::scan_for($token, '\Chronic\Repeater\RepeaterDayName', array(
            '/^m[ou]n(day)?$/' => 'monday',
            '/^t(ue|eu|oo|u|)s?(day)?$/' => 'tuesday',
            '/^we(d|dnes|nds|nns)(day)?$/' => 'wednesday',
            '/^th(u|ur|urs|ers)(day)?$/' => 'thursday',
            '/^fr[iy](day)?$/' => 'friday',
            '/^sat(t?[ue]rday)?$/' => 'saturday',
            '/^su[nm](day)?$/' => 'sunday'
        ));
    }

    public static function scan_for_day_portions($token)
    {
        return self::scan_for($token, '\Chronic\Repeater\RepeaterDayPortion', array(
            '/^ams?$/' => 'am',
            '/^pms?$/' => 'pm',
            '/^mornings?$/' => 'morning',
            '/^afternoons?$/' => 'afternoon',
            '/^evenings?$/' => 'evening',
            '/^(night|nite)s?$/' => 'night'
        ));
    }

    public static function scan_for_times($token)
    {
        return self::scan_for($token, '\Chronic\Repeater\RepeaterTime', '/^\d{1,2}(:?\d{2})?([\.:]?\d{2})?$/');
    }

    public static function scan_for_units($token)
    {
        $units = array(
            '/^years?$/' => 'year',
            '/^seasons?$/' => 'season',
            '/^months?$/' => 'month',
            '/^fortnights?$/' => 'fortnight',
            '/^weeks?$/' => 'week',
            '/^weekends?$/' => 'weekend',
            '/^(week|business)days?$/' => 'weekday',
            '/^days?$/' => 'day',
            '/^hours?$/' => 'hour',
            '/^minutes?$/' => 'minute',
            '/^seconds?$/' => 'second'
        );

        foreach($units as $regex => $symbol)
        {
            if(preg_match($regex, $token->word) > 0)
            {
                $klass_name = 'Repeater'.ucfirst($symbol);
                return new $klass_name($symbol);
            }
        }

        return null;
    }

    public function space_ship_op($other)
    {
        if ($this->width() < $other) return -1;
        if ($this->width() == $other) return 0;
        if ($this->width() > $other) return 1;
        return null;
    }

    /**
     * returns the width (in seconds or months) of this repeatable.
     * @throws Exception
     */
    public function width()
    {
        throw new Exception("Repeater#width must be overridden in subclasses");
    }

    /**
     * returns the next occurance of this repeatable.
     * @throws Exception
     */
//    public function next()
//    {
//        if(! $this->_now)
//            throw new Exception("Start point must be set before calling #next");
//    }

    public function this()
    {
        if(! $this->now)
            throw new Exception("Start point must be set before calling #this");
    }

    public function __toString()
    {
        return 'repeater';
    }
}

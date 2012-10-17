<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class Scalar extends Tag
{
    static $DAY_PORTIONS = array('am', 'pm', 'morning', 'afternoon', 'evening', 'night');

    static function scan(&$tokens, $options)
    {
        for ($i=0, $len = sizeof($tokens); $i < $len; $i++)
        {
            if ($t = self::scanForScalars($tokens[$i], $tokens[$i + 1])){ $tokens[$i]->tag($t); continue; }
        }
    }

    static function scanForScalars(Token $token, $postToken)
    {
        if(preg_match('/^\d*$/', $token->getWord()))
        {
            if( ! ($postToken && in_array($token->getWord(), self::$DAY_PORTIONS)))
            {
                return new Scalar((int)$token->getWord());
            }
        }

        return null;
    }

    static function scanForDays(Token $token, $postToken)
    {
        if(preg_match('/^\d\d?$/', $token->getWord()))
        {
            $toi = (int)$token->getWord();

            if ( ! ($toi > 31 || $toi < 1 || ($postToken && in_array($postToken->getWord(), self::$DAY_PORTIONS))))
            {
                return new ScalarDay($toi);
            }
        }

        return null;
    }

    static function scanForMonths(Token $token, $postToken)
    {
        if(preg_match('/^\d\d?$/', $token->getWord()))
        {
            $toi = (int)$token->getWord();

            if ( ! ($toi > 12 || $toi < 1 || ($postToken && in_array($postToken->getWord(), self::$DAY_PORTIONS))))
            {
                return new ScalarMonth($toi);
            }
        }

        return null;
    }

    static function scanForYears(Token $token, $postToken, $options)
    {
        if(preg_match('/^([1-9]\d)?\d\d?$/', $token->getWord()))
        {
            if( ! ($postToken && in_array($token->getWord(), self::$DAY_PORTIONS)))
            {
                $year = self::makeYear((int)$token->getWord(), $options['ambiguous_year_future_bias']);
                return new ScalarYear($year);
            }
        }

        return null;
    }

    static function makeYear($year, $bias)
    {
        if (strlen($year) > 2) return $year;

        $now = getdate();
        $startYear = $now['year'] - $bias;
        $century = ($startYear / 100) * 100;
        $fullYear = $century + $year;

        if ($fullYear < $startYear)
            $fullYear += 100;

        return $fullYear;
    }
}

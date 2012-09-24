<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

use StringScanner;

class Numerizer
{

    public static $DIRECT_NUMS = array(
        array('eleven', '11'),
        array('twelve', '12'),
        array('thirteen', '13'),
        array('fourteen', '14'),
        array('fifteen', '15'),
        array('sixteen', '16'),
        array('seventeen', '17'),
        array('eighteen', '18'),
        array('nineteen', '19'),
        array('ninteen', '19'), // Common mis-spelling
        array('zero', '0'),
        array('one', '1'),
        array('two', '2'),
        array('three', '3'),
        array('four(\W|$)', '4\1'), // The weird regex is so that it matches four but not fourty
        array('five', '5'),
        array('six(\W|$)', '6\1'),
        array('seven(\W|$)', '7\1'),
        array('eight(\W|$)', '8\1'),
        array('nine(\W|$)', '9\1'),
        array('ten', '10'),
        array('\baarray(\b^$)', '1') // doesn't make sense for an 'a' at the end to be a 1
    );

    public static $ORDINALS = array(
        array('first', '1'),
        array('third', '3'),
        array('fourth', '4'),
        array('fifth', '5'),
        array('sixth', '6'),
        array('seventh', '7'),
        array('eighth', '8'),
        array('ninth', '9'),
        array('tenth', '10')
    );

    public static $TEN_PREFIXES = array(
        array('twenty', 20),
        array('thirty', 30),
        array('forty', 40),
        array('fourty', 40), // Common mis-spelling
        array('fifty', 50),
        array('sixty', 60),
        array('seventy', 70),
        array('eighty', 80),
        array('ninety', 90)
    );

    public static $BIG_PREFIXES = array(
        array('hundred', 100),
        array('thousand', 1000),
        array('million', 1000000),
        array('billion', 1000000000),
        array('trillion', 1000000000000),
    );

    public static function numerize($text)
    {
        // preprocess
        $text = preg_replace('/ +|([^\d])-([^\d])/', '\1 \2', $text); // will mutilate hyphenated-words but shouldn't matter for date extraction
        $text = preg_replace('/a half/', 'haAlf', $text); // take the 'a' out so it doesn't turn into a 1, save the half for the end

        // easy/direct replacements
        foreach( self::$DIRECT_NUMS as $dn)
        {
            $text = preg_replace('/'.$dn[0].'/i', '<num>'.$dn[1], $text);
        }

        foreach(self::$ORDINALS as $on)
        {
            $text = preg_replace('/'.$on[0].'/i', '<num>'.$on[1].substr($on[0], -2, 2), $text);
        }

        // ten, twenty, etc.
        foreach(self::$TEN_PREFIXES as $tp)
        {
            $text = preg_replace_callback(
                '/(?:'.$tp[0].') *<num>(\d(?=[^\d]|$))*/i',
                function($matches) use ($tp){
                    return '<num>'.($tp[1] + (int)$matches[1]);
                },
                $text
            );
        }

        foreach(self::$TEN_PREFIXES as $tp)
        {
            $text = preg_replace('/'.$tp[0].'/i', '<num>'.$tp[1], $text);
        }


        // hundreds, thousands, millions, etc.
        foreach(self::$BIG_PREFIXES as $bp)
        {
            $text = preg_replace_callback(
                '/(?:<num>)?(\d*) *'.$bp[0].'/i',
                function($matches) use ($bp, &$do_andition) {
                    return '<num>'.($bp[1] * (int)$matches[1]);
                },
                $text
            );

            self::andition($text);
        }


        // fractional addition
        // I'm not combining this with the previous block as using float addition complicates the strings
        // (with extraneous .0's and such )
        $text = preg_replace_callback(
            '/(\d+)(?: | and |-)*haAlf/i',
            function($matches){
                return (float)$matches[0] + 0.5;
            },
            $text
        );

        $text = preg_replace('/<num>/', '', $text);

        return $text;
    }

    private static function andition(&$text)
    {
        $sc = new StringScanner($text);

        while($sc->scan_until('/<num>(\d+)( | and )<num>(\d+)(?=[^\w]|$)/i'))
        {
            if(preg_match('/and/', $sc[2]) || strlen($sc[1]) > strlen($sc[3]))
            {
                $text = substr_replace($text, '<num>'.((int)$sc[1] + (int)$sc[3]), $sc->pos - $sc->matched_size, $sc->matched_size );
                $sc->string = $text;
            }
        }
    }
}

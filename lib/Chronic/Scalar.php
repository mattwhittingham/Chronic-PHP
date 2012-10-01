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


}

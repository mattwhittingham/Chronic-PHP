<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */
namespace Chronic;

class Grabber extends \Chronic\Tag
{
    /**
     * Scan an Array of Tokens and apply any necessary Grabber tags to
     * each token.
     *
     * @static
     * @param $tokens - An Array of Token objects to scan.
     * @param $options - The Hash of options specified in Chronic::parse.
     * @return mixed - an Array of Token objects.
     */
    static function scan(&$tokens, $options)
    {
        foreach ($tokens as $token)
        {
            if($t = self::scanForAll($token))
                $token->tag($t);
        }
    }

    static function scanForAll($token)
    {
        return self::scan_for($token, '\Chronic\Grabber', array(
            '/last/' => 'last',
            '/this/' => 'this',
            '/next/' => 'next'
        ));
    }

    function __toString()
    {
        return 'grabber-'.$this->type;
    }
}

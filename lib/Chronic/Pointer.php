<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class Pointer extends \Chronic\Tag
{
    static function scan(&$tokens, $options)
    {
        foreach($tokens as $token)
        {
            if ($t = self::scanForAll($token)){ $token->tag($t); continue; }
        }
    }

    static function scanForAll($token)
    {
        return self::scan_for($token, '\Chronic\Pointer', array('/\bpast\b/' => 'past', '/\b(?:future|in)\b/' => 'future'));
    }

    function __toString()
    {
        return 'pointer-'.$this->getType();
    }
}

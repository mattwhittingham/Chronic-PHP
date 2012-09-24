<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

/**
 * Tokens are tagged with subclassed instances of this class when
 * they match specific criteria.
 */
class Tag
{
    protected $type;
    protected  $now;

    /**
     * @param $type - The Symbol type of this tag.
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param $time - Set the start time for this Tag.
     */
    public function start($time)
    {
        $this->now = $time;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public static function scan_for(Token $token, $klass, $items = array())
    {
        if(is_array($items))
        {
            foreach($items as $item => $symbol)
            {
                if (preg_match($item, $token->getWord()))
                    return new $klass($symbol);
            }
        }
        else
        {
            if (preg_match($items, $token->getWord()))
                return new $klass($token->getWord());
        }

        return null;
    }
}

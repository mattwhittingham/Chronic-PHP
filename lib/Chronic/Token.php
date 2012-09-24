<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

class Token
{
    protected
        $_word,
        $_tags;

    public function __construct($word)
    {
        $this->_word = $word;
        $this->_tags = array();
    }

    /**
     * Tag this token with the specified tag.
     * @param $new_tag
     */
    public function tag($new_tag)
    {
        $this->_tags[] = $new_tag;
    }

    /**
     * Remove all tags of the given class.
     * @param $tag_class
     */
    public function untag($tag_class)
    {
        // remove line below when php 5.4 is installed
        $tags = &$this->_tags;
        array_walk($this->_tags, function($val,$key) use ($tag_class, &$tags){
            $type = gettype($val);
            if(($type == 'object' && is_a($val, $tag_class)) || $type == $tag_class)
                unset($tags[$key]);
        });
    }

    /**
     * Returns true if this token has any tags
     * @return bool
     */
    public function tagged()
    {
        return count($this->_tags) > 0;
    }

    /**
     * @param $tag_class - The tag Class to search for
     * @return The first Tag that matches the given class
     */
    public function getTag($tag_class)
    {
        foreach($this->_tags as $tag)
        {
            $type = gettype($tag);

            if(($type == 'object' && is_a($tag, $tag_class)) || $type == $tag_class)
                return $tag;
        }
    }

    /**
     * @return string Print the token in a pretty way
     */
    public function __toString()
    {
        return $this->_word.'('.implode(', ', $this->_tags).') ';
    }

    public function setTags($tags)
    {
        $this->_tags = $tags;
    }

    public function getTags()
    {
        return $this->_tags;
    }

    public function setWord($word)
    {
        $this->_word = $word;
    }

    public function getWord()
    {
        return $this->_word;
    }
}

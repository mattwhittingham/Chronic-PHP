<?php
/**
 * This program is free software. It comes without any warranty, to 
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;


class Handler
{
    private $_pattern;

    private $_handler_method;

    public function __construct($pattern, $handler_method)
    {
        $this->_pattern = $pattern;
        $this->_handler_method = $handler_method;
    }

    public function match($tokens, $definitions)
    {
        $token_index = 0;

        foreach($this->_pattern as $element)
        {
            $name = $element;
            $optional = substr($name, -1, 1) == '?';

            if($optional)
                $name = substr($name, 0, -1);


            if ( ! is_string($element)) {
                throw new \Exception('Invalid match type: '.gettype($element));
            }

            // is symbol
            if (substr($element, 0, 1) == ':') 
            {
                if ($this->tags_match($name, $tokens, $token_index))
                {
                    $token_index++;
                    continue;
                }
                elseif( $optional ){
                    continue;
                } else {
                    return false;
                }
            }
            else{
                if($optional && $token_index === count($tokens))
                    return true;

                // TODO: Finish this section
                if(array_key_exists($name, $definitions)){
                    $sub_handlers = $definitions[$name];
                }
                else {
                    throw new \Exception("Invalid subset $name specified");
                }

                foreach($sub_handlers as $sub_handler)
                    if($sub_handler->match(array_slice($tokens, $token_index, count($tokens)), $definitions))
                        return true;
                break;
            }
        }

        return $token_index === count($tokens);
    }

    public function invoke($type, $tokens, $options)
    {
        return call_user_func($type, $tokens, $options);
    }
     
    # other - The other Handler object to compare.
    #
    # Returns true if these Handlers match.
    public function equals($other)
    {
    return $this->_pattern == $other->getPattern();
    }
 
    private function tags_match($name, $tokens, $token_index)
    {
        $klass = preg_replace_callback('/(?:^|_)(.)/', function($matches){ return strtoupper($matches[0]); }, ltrim($name, ':'));
        $klass = \Chronic::fullyQualifiedNameSpaceLookup($klass);

        if (isset($tokens[$token_index]))
        {
            foreach ($tokens[$token_index]->getTags() as $tag)
            {
                return $tag instanceof $klass;
            }
        }

        return false;
    }

    public function getHandlerMethod()
    {
        return $this->_handler_method;
    }

    public function getPattern()
    {
        return $this->_pattern;
    }


}

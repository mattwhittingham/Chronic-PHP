<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic;

use Chronic\Tag;

class Separator extends Tag
{
    static function scan(&$tokens, $options)
    {
        foreach($tokens as $token)
        {
            if ($t = self::scanForCommas($token)){ $token->tag($t); continue; }
            if ($t = self::scanForSlashOrDash($token)){ $token->tag($t); continue; }
            if ($t = self::scanForAt($token)){ $token->tag($t); continue; }
            if ($t = self::scanForIn($token)){ $token->tag($t); continue; }
            if ($t = self::scanForOn($token)){ $token->tag($t); continue; }
        }
    }

    static function scanForCommas($token)
    {
        return self::scan_for($token, '\Chronic\SeparatorComma', array('/^,$/' => 'comma'));
    }

    static function scanForSlashOrDash($token)
    {
        return self::scan_for($token, '\Chronic\SeparatorSlashOrDash', array('/^-$/' => 'dash', '/^\/$/' => 'slash'));
    }

    static function scanForAt($token)
    {
        return self::scan_for($token, '\Chronic\SeparatorAt', array('/^(at|@)$/' => 'at'));
    }

    static function scanForIn($token)
    {
        return self::scan_for($token, '\Chronic\SeparatorIn', array('/^in$/' => 'in'));
    }

    static function scanForOn($token)
    {
        return self::scan_for($token, '\Chronic\SeparatorOn', array('/^on$/' => 'on'));
    }

    function __toString()
    {
        return 'separator';
    }
}

class SeparatorComma extends Separator
{
    function __toString()
    {
        return parent::__toString().'-comma';
    }
}

class SeparatorSlashOrDash extends Separator
{
    function __toString()
    {
        return parent::__toString().'-slashordash-'.$this->getType();

    }
}

class SeparatorAt extends Separator
{
    function __toString()
    {
        return parent::__toString().'-at';
    }
}
class SeparatorIn extends Separator
{
    function __toString()
    {
        return parent::__toString().'-in';
    }
}

class SeparatorOn extends Separator
{
    function __toString()
    {
        return parent::__toString().'-on';
    }
}


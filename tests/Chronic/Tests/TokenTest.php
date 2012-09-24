<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */

namespace Chronic\Tests;

use Chronic\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{

    function testToken()
    {
        $token = new Token('foo');
        $this->assertEquals(0, count($token->getTags()));
        $this->assertTrue(! $token->tagged());

        $token->tag('mytag');
        $this->assertEquals(1, count($token->getTags()));
        $this->assertTrue($token->tagged());
        $this->assertEquals('string', gettype($token->getTag('string')));

        $token->tag(5);
        $this->assertEquals(2, count($token->getTags()));

        $token->untag('string');
        $this->assertEquals(1, count($token->getTags()));
        $this->assertEquals('foo', $token->getWord());
    }
}

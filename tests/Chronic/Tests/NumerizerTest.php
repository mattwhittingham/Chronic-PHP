<?php

namespace Chronic\Tests;

use Chronic\Numerizer;
use Chronic;

class NumerizerTest extends \PHPUnit_Framework_TestCase
{
    static function straightCases()
    {
        $strings = array(
            'one' => 1,
            'five' => 5,
            'ten' => 10,
            'eleven' => 11,
            'twelve' => 12,
            'thirteen' => 13,
            'fourteen' => 14,
            'fifteen' => 15,
            'sixteen' => 16,
            'seventeen' => 17,
            'eighteen' => 18,
            'nineteen' => 19,
            'twenty' => 20,
            'twenty seven' => 27,
            'thirty-one' => 31,
            'thirty-seven' => 37,
            'thirty seven' => 37,
            'fifty nine' => 59,
            'forty two' => 42,
            'fourty two' => 42,
            # 'a hundred' => 100,
            'one hundred' => 100,
            'one hundred and fifty' => 150,
            # 'one fifty' => 150,
            'two-hundred' => 200,
            '5 hundred' => 500,
            'nine hundred and ninety nine' => 999,
            'one thousand' => 1000,
            'twelve hundred' => 1200,
            'one thousand two hundred' => 1200,
            'seventeen thousand' => 17000,
            'twentyone-thousand-four-hundred-and-seventy-three' => 21473,
            'seventy four thousand and two' => 74002,
            'ninety nine thousand nine hundred ninety nine' => 99999,
            '100 thousand' => 100000,
            'two hundred fifty thousand' => 250000,
            'one million' => 1000000,
            'one million two hundred fifty thousand and seven' => 1250007,
            'one billion' => 1000000000,
            'one billion and one' => 1000000001
        );

        return array_map(function($x,$y){ return array($x,$y);}, array_keys($strings), array_values($strings));
    }

    /**
     * @dataProvider straightCases
     */
    function testStraightParsing($key, $val)
    {
       $this->assertEquals($val, Numerizer::numerize($key));
    }

    function ordinalCases()
    {
        $cases = array(
            'first' => '1st',
            'second' => 'second',
            'second day' => '2nd day',
            'second of may' => '2nd of may',
            'fifth' => '5th',
            'twenty third' => '23rd',
            'first day month two' => '1st day month 2'
        );

        return array_map(function($x,$y){ return array($x,$y);}, array_keys($cases), array_values($cases));
    }

    /**
     * @dataProvider ordinalCases
     */
    function testOrdinalStrings($key, $val)
    {
        $this->assertEquals($val, Chronic::preNormalize($key));
    }

    function testEdges()
    {
        $this->assertEquals("27 Oct 2006 7:30am", Numerizer::numerize("27 Oct 2006 7:30am"));
    }
}

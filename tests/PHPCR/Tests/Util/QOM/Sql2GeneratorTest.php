<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql2Generator;

class Sql2GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    public function setUp()
    {
        $this->generator = new Sql2Generator();
    }

    public function testLiteral()
    {
        $literal = $this->generator->evalLiteral('Foobar');
        $this->assertEquals("'Foobar'", $literal);
    }

    public function testDateTimeLiteral()
    {
        $literal = $this->generator->evalLiteral(new \DateTime('2011-12-23T00:00:00.000+00:00'));
        $this->assertEquals("CAST('2011-12-23T00:00:00.000+00:00' AS DATE)", $literal);
    }
    public function testBoolLiteral()
    {
        $literal = $this->generator->evalLiteral(true);
        $this->assertEquals("CAST('true' AS BOOLEAN)", $literal);
    }

    public function testLongLiteral()
    {
        $literal = $this->generator->evalLiteral(11);
        $this->assertEquals("CAST('11' AS LONG)", $literal);
    }

    public function testDoubleLiteral()
    {
        $literal = $this->generator->evalLiteral(11.0);
        $this->assertEquals("CAST('11' AS DOUBLE)", $literal);
    }
}

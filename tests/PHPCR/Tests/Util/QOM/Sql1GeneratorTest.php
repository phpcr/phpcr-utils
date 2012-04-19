<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql1Generator;

class Sql1GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    public function setUp()
    {
        $this->generator = new Sql1Generator();
    }

    public function testLiteral()
    {
        $literal = $this->generator->evalLiteral('Foobar');
        $this->assertEquals("'Foobar'", $literal);
    }

    public function testDateTimeLiteral()
    {
        $literal = $this->generator->evalLiteral(new \DateTime('2011-12-23T00:00:00.000+00:00'));
        $this->assertEquals("TIMESTAMP '2011-12-23T00:00:00.000+00:00'", $literal);
    }

    public function testBoolLiteral()
    {
        $literal = $this->generator->evalLiteral(true);
        $this->assertEquals("'true'", $literal);
    }

    public function testLongLiteral()
    {
        $literal = $this->generator->evalLiteral(11);
        $this->assertSame("11", $literal);
    }

    public function testDoubleLiteral()
    {
        $literal = $this->generator->evalLiteral(11.0);
        $this->assertSame("11.0", $literal);
    }
}

<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql2Generator;
use PHPCR\Util\ValueConverter;

class Sql2GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    public function setUp()
    {
        $this->generator = new Sql2Generator(new ValueConverter());
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

    public function testChildNode()
    {
        $literal = $this->generator->evalChildNode("/foo/bar/baz");
        $this->assertSame("ISCHILDNODE(/foo/bar/baz)", $literal);
    }

    public function testDescendantNode()
    {
        $literal = $this->generator->evalDescendantNode("/foo/bar/baz");
        $this->assertSame("ISDESCENDANTNODE(/foo/bar/baz)", $literal);
    }

    public function testPopertyExistence()
    {
        $literal = $this->generator->evalPropertyExistence(null, "foo");
        $this->assertSame("foo IS NOT NULL", $literal);
    }

    public function testFullTextSearch()
    {
        $literal = $this->generator->evalFullTextSearch("data", "'foo'");
        $this->assertSame("CONTAINS(data.*, 'foo')", $literal);
        $literal = $this->generator->evalFullTextSearch("data", "'foo'", "bar");
        $this->assertSame("CONTAINS(data.bar, 'foo')", $literal);
    }

    public function testColumns()
    {
        $literal = $this->generator->evalColumns(null);
        $this->assertSame("*", $literal);
        $literal = $this->generator->evalColumns(array("bar","foo"));
        $this->assertSame("bar, foo", $literal);
    }

    public function testPropertyValue()
    {
        $literal = $this->generator->evalPropertyValue("foo");
        $this->assertSame("foo", $literal);
    }
}

<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql1Generator;
use PHPCR\Util\ValueConverter;

class Sql1GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $generator;

    public function setUp()
    {
        $this->generator = new Sql1Generator(new ValueConverter());
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

    public function testChildNode()
    {
        $literal = $this->generator->evalChildNode("/");
        $this->assertSame("jcr:path LIKE '/%' AND NOT jcr:path LIKE '/%/%'", $literal);

        $literal = $this->generator->evalChildNode("/foo/bar/baz");
        $this->assertSame("jcr:path LIKE '/foo[%]/bar[%]/baz[%]/%' AND NOT jcr:path LIKE '/foo[%]/bar[%]/baz[%]/%/%'", $literal);
    }

    public function testDescendantNode()
    {
        $literal = $this->generator->evalDescendantNode("/");
        $this->assertSame("jcr:path LIKE '/%'", $literal);

        $literal = $this->generator->evalDescendantNode("/foo/bar/baz");
        $this->assertSame("jcr:path LIKE '/foo[%]/bar[%]/baz[%]/%'", $literal);
    }

    public function testPopertyExistence()
    {
        $literal = $this->generator->evalPropertyExistence(null, "foo");
        $this->assertSame("foo IS NOT NULL", $literal);
    }

    public function testFullTextSearch()
    {
        $literal = $this->generator->evalFullTextSearch(null, "'foo'");
        $this->assertSame("CONTAINS(*, 'foo')", $literal);
        $literal = $this->generator->evalFullTextSearch(null, "'foo'", "bar");
        $this->assertSame("CONTAINS(bar, 'foo')", $literal);
    }

    public function testColumns()
    {
        $literal = $this->generator->evalColumns(null);
        $this->assertSame("s", $literal);
        $literal = $this->generator->evalColumns(array("bar","foo"));
        $this->assertSame("bar, foo", $literal);
    }

    public function testPropertyValue()
    {
        $literal = $this->generator->evalPropertyValue("foo");
        $this->assertSame("foo", $literal);
    }
}

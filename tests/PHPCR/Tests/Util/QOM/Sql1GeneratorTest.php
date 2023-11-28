<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql1Generator;
use PHPCR\Util\ValueConverter;
use PHPUnit\Framework\TestCase;

class Sql1GeneratorTest extends TestCase
{
    /**
     * @var Sql1Generator
     */
    protected $generator;

    public function setUp(): void
    {
        $this->generator = new Sql1Generator(new ValueConverter());
    }

    public function testLiteral(): void
    {
        $literal = $this->generator->evalLiteral('Foobar');
        $this->assertEquals("'Foobar'", $literal);
    }

    public function testDateTimeLiteral(): void
    {
        $literal = $this->generator->evalLiteral(new \DateTime('2011-12-23T00:00:00.000+00:00'));
        $this->assertEquals("TIMESTAMP '2011-12-23T00:00:00.000+00:00'", $literal);
    }

    public function testBoolLiteral(): void
    {
        $literal = $this->generator->evalLiteral(true);
        $this->assertEquals("'true'", $literal);
    }

    public function testLongLiteral(): void
    {
        $literal = $this->generator->evalLiteral(11);
        $this->assertSame('11', $literal);
    }

    public function testDoubleLiteral(): void
    {
        $literal = $this->generator->evalLiteral(11.0);
        $this->assertSame('11.0', $literal);
    }

    public function testChildNode(): void
    {
        $literal = $this->generator->evalChildNode('/');
        $this->assertSame("jcr:path LIKE '/%' AND NOT jcr:path LIKE '/%/%'", $literal);

        $literal = $this->generator->evalChildNode('/foo/bar/baz');
        $this->assertSame("jcr:path LIKE '/foo[%]/bar[%]/baz[%]/%' AND NOT jcr:path LIKE '/foo[%]/bar[%]/baz[%]/%/%'", $literal);
    }

    public function testDescendantNode(): void
    {
        $literal = $this->generator->evalDescendantNode('/');
        $this->assertSame("jcr:path LIKE '/%'", $literal);

        $literal = $this->generator->evalDescendantNode('/foo/bar/baz');
        $this->assertSame("jcr:path LIKE '/foo[%]/bar[%]/baz[%]/%'", $literal);
    }

    public function testPopertyExistence(): void
    {
        $literal = $this->generator->evalPropertyExistence(null, 'foo');
        $this->assertSame('foo IS NOT NULL', $literal);
    }

    public function testFullTextSearch(): void
    {
        $literal = $this->generator->evalFullTextSearch('', "'foo'");
        $this->assertSame("CONTAINS(*, 'foo')", $literal);
        $literal = $this->generator->evalFullTextSearch('', "'foo'", 'bar');
        $this->assertSame("CONTAINS(bar, 'foo')", $literal);
    }

    public function testColumns(): void
    {
        $literal = $this->generator->evalColumns([]);
        $this->assertSame('s', $literal);
        $literal = $this->generator->evalColumns(['bar', 'foo']);
        $this->assertSame('bar, foo', $literal);
    }

    public function testPropertyValue(): void
    {
        $literal = $this->generator->evalPropertyValue('foo');
        $this->assertSame('foo', $literal);
    }
}

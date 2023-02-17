<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\BaseSqlGenerator;
use PHPUnit\Framework\TestCase;

abstract class BaseSqlGeneratorTest extends TestCase
{
    /**
     * @var BaseSqlGenerator
     */
    protected $generator;

    public function testNot()
    {
        $string = $this->generator->evalNot('foo = bar');
        $this->assertSame('(NOT foo = bar)', $string);
    }
}

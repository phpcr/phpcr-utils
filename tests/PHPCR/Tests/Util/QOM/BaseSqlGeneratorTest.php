<?php

namespace PHPCR\Tests\Util\QOM;

use PHPUnit\Framework\TestCase;

abstract class BaseSqlGeneratorTest extends TestCase
{
    public function testNot()
    {
        $string = $this->generator->evalNot('foo = bar');
        $this->assertSame('(NOT foo = bar)', $string);
    }
}

<?php

namespace PHPCR\Tests\Util\QOM;

use PHPUnit_Framework_TestCase;

abstract class BaseSqlGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testNot()
    {
        $string = $this->generator->evalNot('foo = bar');
        $this->assertSame('(NOT foo = bar)', $string);
    }
}

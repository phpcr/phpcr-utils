<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\BaseSqlGenerator;
use PHPUnit\Framework\TestCase;

abstract class BaseSqlGeneratorTest extends TestCase
{
    /**
     * @var BaseSqlGenerator
     */
    protected $generator;

    public function testNot(): void
    {
        $string = $this->generator->evalNot('foo = bar');
        $this->assertSame('(NOT foo = bar)', $string);
    }
}

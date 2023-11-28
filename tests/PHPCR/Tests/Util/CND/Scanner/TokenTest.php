<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\CND\Scanner;

use PHPCR\Util\CND\Scanner\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    /**
     * @var Token
     */
    private $token;

    public function setUp(): void
    {
        $this->token = new Token(123, 'foobar');
    }

    public function testConstruct(): void
    {
        $this->assertSame(123, $this->token->type);
        $this->assertSame('foobar', $this->token->data);
    }

    public function testGetData(): void
    {
        $this->assertEquals('foobar', $this->token->getData());
    }

    public function testGetType(): void
    {
        $this->assertEquals(123, $this->token->getType());
    }

    public function testToString(): void
    {
        $this->assertEquals('TOKEN(123, \'foobar\', 0, 0)', $this->token->__toString());
    }
}

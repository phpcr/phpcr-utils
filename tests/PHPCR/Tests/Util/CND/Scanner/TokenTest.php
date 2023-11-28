<?php

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

    public function testConstruct()
    {
        $this->assertSame(123, $this->token->type);
        $this->assertSame('foobar', $this->token->data);
    }

    public function testGetData()
    {
        $this->assertEquals('foobar', $this->token->getData());
    }

    public function testGetType()
    {
        $this->assertEquals(123, $this->token->getType());
    }

    public function testToString()
    {
        $this->assertEquals('TOKEN(123, \'foobar\', 0, 0)', $this->token->__toString());
    }
}

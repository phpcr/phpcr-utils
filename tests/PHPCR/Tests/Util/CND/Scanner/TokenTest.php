<?php

namespace PHPCR\Tests\Util\CND\Scanner;

use PHPCR\Util\CND\Scanner\Token;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->token = new Token(123, 'foobar');
    }

    public function test__construct()
    {
        $this->assertAttributeEquals(123, 'type', $this->token);
        $this->assertAttributeEquals('foobar', 'data', $this->token);
    }

    public function testGetData()
    {
        $this->assertEquals('foobar', $this->token->getData());
    }

    public function testGetType()
    {
        $this->assertEquals(123, $this->token->getType());
    }

    public function test__toString()
    {
        $this->assertEquals('TOKEN(123, \'foobar\', 0, 0)', $this->token->__toString());
    }

}

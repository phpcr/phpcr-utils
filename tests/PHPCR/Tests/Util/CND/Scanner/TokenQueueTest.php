<?php

namespace PHPCR\Tests\Util\CND\Scanner;

use PHPCR\Util\CND\Scanner\Token,
    PHPCR\Util\CND\Scanner\TokenQueue;

class TokenQueueTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->token0 = new Token(0, 'token 0');
        $this->token1 = new Token(1, 'token 1');
        $this->token2 = new Token(2, 'token 2');
        $this->token3 = new Token(3, 'token 3');

        $this->queue = new TokenQueue();
        $this->queue->add($this->token0);
        $this->queue->add($this->token1);
        $this->queue->add($this->token2);
        $this->queue->add($this->token3);
    }

    public function testAdd()
    {
        $queue = new TokenQueue();
        $this->assertAttributeEquals(array(), 'tokens', $queue);

        $queue->add($this->token0);
        $this->assertAttributeEquals(array($this->token0), 'tokens', $queue);

        $queue->add($this->token1);
        $this->assertAttributeEquals(array($this->token0, $this->token1), 'tokens', $queue);
    }

    public function testResetAndPeek()
    {
        $this->assertEquals($this->token0, $this->queue->reset());
        $this->assertEquals($this->token0, $this->queue->peek());
    }

    public function testIsEofAndNext()
    {
        // Token0
        $this->assertFalse($this->queue->isEof());

        // Token1
        $this->queue->next();
        $this->assertFalse($this->queue->isEof());

        // Token2
        $this->queue->next();
        $this->assertFalse($this->queue->isEof());

        // Token3
        $this->queue->next();
        $this->assertFalse($this->queue->isEof());

        // EOF
        $this->queue->next();
        $this->assertTrue($this->queue->isEof());
    }

    public function testIsEofEmptyQueue()
    {
        $queue = new TokenQueue();
        $this->assertTrue($queue->isEof());
        $queue->add(new Token(0, 'token'));
        $this->assertFalse($queue->isEof());
    }

    public function testGet()
    {
        $this->queue->reset();
        $this->assertEquals($this->token0, $this->queue->get());
        $this->assertEquals($this->token1, $this->queue->get());
        $this->assertEquals($this->token2, $this->queue->get());
        $this->assertEquals($this->token3, $this->queue->get());
        $this->assertEquals(false, $this->queue->get());
    }

    public function testGetIterator()
    {
        $this->assertEquals(
            array($this->token0, $this->token1, $this->token2, $this->token3),
            iterator_to_array($this->queue->getIterator())
        );
    }
}

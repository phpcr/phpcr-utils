<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\CND\Scanner;

use PHPCR\Util\CND\Scanner\Token;
use PHPCR\Util\CND\Scanner\TokenQueue;
use PHPUnit\Framework\TestCase;

class TokenQueueTest extends TestCase
{
    /**
     * @var Token
     */
    private $token0;

    /**
     * @var Token
     */
    private $token1;

    /**
     * @var Token
     */
    private $token2;

    /**
     * @var Token
     */
    private $token3;

    /**
     * @var TokenQueue
     */
    private $queue;

    public function setUp(): void
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

    public function testAdd(): void
    {
        $queue = new TokenQueue();
        $reflection = new \ReflectionClass($queue);
        $tokens = $reflection->getProperty('tokens');
        $tokens->setAccessible(true);
        $this->assertSame([], $tokens->getValue($queue));

        $queue->add($this->token0);
        $this->assertSame([$this->token0], $tokens->getValue($queue));

        $queue->add($this->token1);
        $this->assertSame([$this->token0, $this->token1], $tokens->getValue($queue));
    }

    public function testResetAndPeek(): void
    {
        $this->assertEquals($this->token0, $this->queue->reset());
        $this->assertEquals($this->token0, $this->queue->peek());
    }

    public function testIsEofAndNext(): void
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

    public function testIsEofEmptyQueue(): void
    {
        $queue = new TokenQueue();
        $this->assertTrue($queue->isEof());
        $queue->add(new Token(0, 'token'));
        $this->assertFalse($queue->isEof());
    }

    public function testGet(): void
    {
        $this->queue->reset();
        $this->assertEquals($this->token0, $this->queue->get());
        $this->assertEquals($this->token1, $this->queue->get());
        $this->assertEquals($this->token2, $this->queue->get());
        $this->assertEquals($this->token3, $this->queue->get());
        $this->assertEquals(false, $this->queue->get());
    }

    public function testGetIterator(): void
    {
        $this->assertEquals(
            [$this->token0, $this->token1, $this->token2, $this->token3],
            iterator_to_array($this->queue->getIterator())
        );
    }
}

<?php

namespace PHPCR\Util\CND\Scanner;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TokenQueue implements \IteratorAggregate
{
    /**
     * @var Token[]
     */
    protected mixed $tokens;

    /**
     * @param Token[] $tokens
     */
    public function __construct(array $tokens = [])
    {
        $this->tokens = $tokens;
    }

    public function add(Token $token): void
    {
        $this->tokens[] = $token;
    }

    public function reset(): Token
    {
        return reset($this->tokens);
    }

    public function isEof(): bool
    {
        return false === current($this->tokens);
    }

    public function peek($offset = 0): Token|false
    {
        if (!$offset) {
            return current($this->tokens);
        }

        $lookup = key($this->tokens) + $offset;

        if ($lookup >= count($this->tokens)) {
            return false;
        }

        return $this->tokens[key($this->tokens) + $offset];
    }

    public function get($count = 1): Token|null
    {
        $item = null;
        for ($i = 1; $i <= $count; ++$i) {
            $item = $this->peek();
            $this->next();
        }

        return $item ?: null;
    }

    public function next(): Token|false
    {
        return next($this->tokens);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->tokens);
    }
}

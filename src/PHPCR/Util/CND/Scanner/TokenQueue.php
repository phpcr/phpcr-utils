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
     * @var array
     */
    protected $tokens;

    public function __construct($tokens = [])
    {
        $this->tokens = $tokens;
    }

    public function add(Token $token)
    {
        $this->tokens[] = $token;
    }

    public function reset()
    {
        return reset($this->tokens);
    }

    public function isEof()
    {
        return false === current($this->tokens);
    }

    public function peek($offset = 0)
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

    public function get($count = 1)
    {
        $item = null;
        for ($i = 1; $i <= $count; ++$i) {
            $item = $this->peek();
            $this->next();
        }

        return $item;
    }

    public function next()
    {
        return next($this->tokens);
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->tokens);
    }
}

<?php

namespace PHPCR\Util\CND\Scanner;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TokenQueue implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $tokens;

    public function __construct($tokens = array())
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
        return current($this->tokens) === false;
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
        for ($i = 1; $i <= $count; $i++) {
            $item = $this->peek();
            $this->next();
        }

        return $item;
    }

    public function next()
    {
        return next($this->tokens);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->tokens);
    }

    public function debug()
    {
        foreach($this->tokens as $token)
        {
            echo $token . "\n";
        }
    }
}
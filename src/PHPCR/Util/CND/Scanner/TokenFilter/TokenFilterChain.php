<?php

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\Token;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TokenFilterChain implements TokenFilterInterface
{
    /**
     * @var TokenFilterInterface[]
     */
    protected $filters;

    public function addFilter(TokenFilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param Token $token
     * @return Token | null
     */
    function filter(Token $token)
    {
        foreach ($this->filters as $filter) {

            $token = $filter->filter($token);

            if (!$token) {
                return null;
            }
        }

        return $token;
    }
}

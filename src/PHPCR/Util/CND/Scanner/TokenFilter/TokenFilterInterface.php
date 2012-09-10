<?php

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\Token;

interface TokenFilterInterface
{
    /**
     * @abstract
     * @param Token $token
     * @return Token | null
     */
    function filter(Token $token);
}

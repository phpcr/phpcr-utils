<?php

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\Token;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface TokenFilterInterface
{
    /**
     * @abstract
     * @param Token $token
     * @return Token | null
     */
    function filter(Token $token);
}

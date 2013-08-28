<?php

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\Token;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface TokenFilterInterface
{
    /**
     * @abstract
     * @param  Token $token
     * @return Token | null
     */
    public function filter(Token $token);
}

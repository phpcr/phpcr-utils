<?php

declare(strict_types=1);

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\Token;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TokenTypeFilter implements TokenFilterInterface
{
    /**
     * The filtered out token type.
     */
    protected int $type;

    public function __construct(int $tokenType)
    {
        $this->type = $tokenType;
    }

    public function filter(Token $token): ?Token
    {
        if ($token->getType() === $this->type) {
            return null;
        }

        return $token;
    }
}

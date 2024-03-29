<?php

declare(strict_types=1);

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\Token;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TokenFilterChain implements TokenFilterInterface
{
    /**
     * @var TokenFilterInterface[]
     */
    protected array $filters;

    public function addFilter(TokenFilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    public function filter(Token $token): ?Token
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

<?php

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\GenericToken;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NoWhitespacesFilter extends TokenTypeFilter
{
    public function __construct()
    {
        parent::__construct(GenericToken::TK_WHITESPACE);
    }
}

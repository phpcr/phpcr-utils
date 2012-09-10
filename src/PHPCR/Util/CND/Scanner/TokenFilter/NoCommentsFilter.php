<?php

namespace PHPCR\Util\CND\Scanner\TokenFilter;

use PHPCR\Util\CND\Scanner\GenericToken;

class NoCommentsFilter extends TokenTypeFilter
{
    function __construct()
    {
        parent::__construct(GenericToken::TK_COMMENT);
    }
}

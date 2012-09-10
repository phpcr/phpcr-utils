<?php

namespace PHPCR\Util\CND\Scanner\Context;

use PHPCR\Util\CND\Scanner\TokenFilter;

class DefaultScannerContextWithoutSpacesAndComments extends DefaultScannerContext
{
    public function __construct()
    {
        parent::__construct();

        $this->addTokenFilter(new TokenFilter\NoNewlinesFilter());
        $this->addTokenFilter(new TokenFilter\NoWhitespacesFilter());
        $this->addTokenFilter(new TokenFilter\NoCommentsFilter());
    }
    
}

<?php

namespace PHPCR\Util\CND\Scanner\Context;

use PHPCR\Util\CND\Scanner\TokenFilter;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
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

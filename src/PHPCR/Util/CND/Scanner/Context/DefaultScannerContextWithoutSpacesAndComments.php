<?php

namespace PHPCR\Util\CND\Scanner\Context;

use PHPCR\Util\CND\Scanner\TokenFilter;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
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

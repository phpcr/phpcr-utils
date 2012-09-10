<?php

namespace PHPCR\Util\CND\Scanner\Context;

use PHPCR\Util\CND\Scanner\TokenFilter\TokenFilterInterface;

class DefaultScannerContext extends ScannerContext
{
    public function __construct()
    {
        $this->addWhitespace(" ");
        $this->addWhitespace("\t");

        $this->addStringDelimiter('\'');
        $this->addStringDelimiter('"');

        $this->addLineCommentDelimiter('//');

        $this->addBlockCommentDelimiter('/*', '*/');

        $symbols = array(
            '<', '>', '+', '*', '%', '&', '/', '(', ')', '=', '?', '#', '|', '!', '~',
            '[', ']', '{', '}', '$', ',', ';', ':', '.', '-', '_', '\\',
        );
        foreach($symbols as $symbol) {
            $this->addSymbol($symbol);
        }
    }
}

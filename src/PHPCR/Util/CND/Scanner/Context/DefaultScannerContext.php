<?php

namespace PHPCR\Util\CND\Scanner\Context;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
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
        foreach ($symbols as $symbol) {
            $this->addSymbol($symbol);
        }
    }
}

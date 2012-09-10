<?php

namespace PHPCR\Util\CND\Scanner;

use PHPCR\Util\CND\Reader\ReaderInterface;

interface ScannerInterface
{
    /**
     * @abstract
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return TokenQueue
     */
    function scan(ReaderInterface $reader);

    /**
     * @abstract
     * @param Token $token
     * @return Token | void
     */
    function applyFilters(Token $token);

}

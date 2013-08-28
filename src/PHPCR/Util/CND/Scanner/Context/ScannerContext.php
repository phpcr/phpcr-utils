<?php

namespace PHPCR\Util\CND\Scanner\Context;

use PHPCR\Util\CND\Scanner\TokenFilter\TokenFilterInterface;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ScannerContext
{
    /**
     * Characters to be considered as white spaces
     * @var array
     */
    protected $whitespaces = array();

    /**
     * Characters to be considered as paired string delimiters.
     *
     * These characters will not be used as symbols, thus if you remove any from this list,
     * you must add it to the $symbols array to be taken in account as a symbol.
     *
     * @var array
     */
    protected $stringDelimiters = array();

    /**
     * Line comments start
     *
     * @var array
     */
    protected $lineCommentDelimiters = array();

    /**
     * Block comments delimiters
     *
     * @var array
     */
    protected $blockCommentDelimiters = array();

    /**
     * Characters to be considered as symbols.
     *
     * String delimiters must not appear in this array.
     *
     * @var array
     */
    protected $symbols = array();

    /**
     * @var TokenFilterInterface[]
     */
    protected $tokenFilters = array();

    /**
     * @param string $startDelim
     * @param string $endDelim
     */
    public function addBlockCommentDelimiter($startDelim, $endDelim)
    {
        $this->blockCommentDelimiters[$startDelim] = $endDelim;
    }

    /**
     * @return array
     */
    public function getBlockCommentDelimiters()
    {
        return $this->blockCommentDelimiters;
    }

    /**
     * @param string $delim
     */
    public function addLineCommentDelimiter($delim)
    {
        $this->lineCommentDelimiters[] = $delim;
    }

    /**
     * @return array
     */
    public function getLineCommentDelimiters()
    {
        return $this->lineCommentDelimiters;
    }

    /**
     * @param string $delim
     */
    public function addStringDelimiter($delim)
    {
        if (!in_array($delim, $this->stringDelimiters)) {
            $this->stringDelimiters[] = $delim;
        }
    }

    /**
     * @return array
     */
    public function getStringDelimiters()
    {
        return $this->stringDelimiters;
    }

    /**
     * @param string $symbol
     */
    public function addSymbol($symbol)
    {
        if (!in_array($symbol, $this->symbols)) {
            $this->symbols[] = $symbol;
        }
    }

    /**
     * @return array
     */
    public function getSymbols()
    {
        return $this->symbols;
    }

    /**
     * @param array $whitespace
     */
    public function addWhitespace($whitespace)
    {
        if (!in_array($whitespace, $this->whitespaces)) {
            $this->whitespaces[] = $whitespace;
        }
    }

    /**
     * @return array
     */
    public function getWhitespaces()
    {
        return $this->whitespaces;
    }

    /**
     * @param TokenFilterInterface $filter
     */
    public function addTokenFilter(TokenFilterInterface $filter)
    {
        $this->tokenFilters[] = $filter;
    }

    /**
     * @return TokenFilterInterface[]
     */
    public function getTokenFilters()
    {
        return $this->tokenFilters;
    }
}

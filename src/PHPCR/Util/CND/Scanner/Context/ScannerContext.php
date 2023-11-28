<?php

namespace PHPCR\Util\CND\Scanner\Context;

use PHPCR\Util\CND\Scanner\TokenFilter\TokenFilterInterface;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ScannerContext
{
    /**
     * Characters to be considered as white spaces.
     *
     * @var string[]
     */
    protected array $whitespaces = [];

    /**
     * Characters to be considered as paired string delimiters.
     *
     * These characters will not be used as symbols, thus if you remove any from this list,
     * you must add it to the $symbols array to be taken in account as a symbol.
     *
     * @var string[]
     */
    protected array $stringDelimiters = [];

    /**
     * Line comments start.
     *
     * @var string[]
     */
    protected array $lineCommentDelimiters = [];

    /**
     * Block comments delimiters.
     *
     * @var string[]
     */
    protected array $blockCommentDelimiters = [];

    /**
     * Characters to be considered as symbols.
     *
     * String delimiters must not appear in this array.
     *
     * @var string[]
     */
    protected array $symbols = [];

    /**
     * @var TokenFilterInterface[]
     */
    protected array $tokenFilters = [];

    public function addBlockCommentDelimiter(string $startDelim, string $endDelim): void
    {
        $this->blockCommentDelimiters[$startDelim] = $endDelim;
    }

    /**
     * @return string[]
     */
    public function getBlockCommentDelimiters(): array
    {
        return $this->blockCommentDelimiters;
    }

    public function addLineCommentDelimiter(string $delim): void
    {
        $this->lineCommentDelimiters[] = $delim;
    }

    /**
     * @return string[]
     */
    public function getLineCommentDelimiters(): array
    {
        return $this->lineCommentDelimiters;
    }

    public function addStringDelimiter(string $delim): void
    {
        if (!in_array($delim, $this->stringDelimiters, true)) {
            $this->stringDelimiters[] = $delim;
        }
    }

    /**
     * @return string[]
     */
    public function getStringDelimiters(): array
    {
        return $this->stringDelimiters;
    }

    public function addSymbol(string $symbol): void
    {
        if (!in_array($symbol, $this->symbols, true)) {
            $this->symbols[] = $symbol;
        }
    }

    /**
     * @return string[]
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    public function addWhitespace(string $whitespace): void
    {
        if (!in_array($whitespace, $this->whitespaces, true)) {
            $this->whitespaces[] = $whitespace;
        }
    }

    /**
     * @return string[]
     */
    public function getWhitespaces(): array
    {
        return $this->whitespaces;
    }

    public function addTokenFilter(TokenFilterInterface $filter): void
    {
        $this->tokenFilters[] = $filter;
    }

    /**
     * @return TokenFilterInterface[]
     */
    public function getTokenFilters(): array
    {
        return $this->tokenFilters;
    }
}

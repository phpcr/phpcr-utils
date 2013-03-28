<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface ReaderInterface
{
    /**
     * @return bool
     */
    public function getEofMarker();

    /**
     * @return string with just one character
     */
    public function currentChar();

    public function isEof();

    /**
     * @return int
     */
    function getCurrentLine();

    /**
     * @return int
     */
    function getCurrentColumn();

    /**
     * Return the literal delimited by start and end position
     * @return string
     */
    public function current();

    /**
     * Advance the forward position and return the literal delimited by start and end position
     * @return string
     */
    public function forward();

    public function forwardChar();

    /**
     * Rewind the forward position to the start position
     * @return void
     */
    public function rewind();

    /**
     * Return the literal delimited by start and end position, then set the
     * start position to the end position
     *
     * @return string
     */
    public function consume();
}

<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface ReaderInterface
{
    /**
     * @return string
     */
    public function getEofMarker();

    /**
     * @return string
     */
    public function getEolMarker();

    /**
     * @return string with just one character
     */
    public function currentChar();

    /**
     * @return bool
     */
    public function isEof();

    /**
     * @return bool
     */
    public function isEol();

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

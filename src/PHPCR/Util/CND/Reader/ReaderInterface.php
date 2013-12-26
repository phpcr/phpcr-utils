<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface ReaderInterface
{
    /**
     * @return string
     */
    public function getEofMarker();

    /**
     * @return string with just one character
     */
    public function currentChar();

    /**
     * @return boolean
     */
    public function isEof();

    /**
     * @return int
     */
    public function getCurrentLine();

    /**
     * @return int
     */
    public function getCurrentColumn();

    /**
     * Return the literal delimited by start and end position
     *
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

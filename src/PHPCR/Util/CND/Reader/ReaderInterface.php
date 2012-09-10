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
     * @return int
     */
    function getCurrentLine();

    /**
     * @return int
     */
    function getCurrentColumn();

    /**
     * @return string
     */
    function getBuffer();

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

    /**
     * Rewind the forward position to the start position
     * @return void
     */
    public function rewind();

    /**
     * Rewind the forward position to its previous position
     * @return void
     */
    public function unget();

    /**
     * Return the literal delimited by start and end position, then set the start position to the end position
     * @return void
     */
    public function consume();

}

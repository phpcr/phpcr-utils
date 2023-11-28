<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface ReaderInterface
{
    public function getEofMarker(): string;

    public function currentChar(): string;

    public function isEof(): bool;

    public function getCurrentLine(): int;

    public function getCurrentColumn(): int;

    /**
     * Return the literal delimited by start and end position.
     */
    public function current(): string;

    /**
     * Advance the forward position and return the literal delimited by start and end position.
     */
    public function forward(): string;

    /**
     * Forward one character and return the character at the new position.
     */
    public function forwardChar(): string;

    /**
     * Rewind the forward position to the start position.
     */
    public function rewind(): void;

    /**
     * Return the literal delimited by start and end position, then set the
     * start position to the end position.
     */
    public function consume(): string;
}

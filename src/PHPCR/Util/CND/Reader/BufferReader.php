<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class BufferReader implements ReaderInterface
{
    private string $eofMarker;

    protected string $buffer;

    protected int $startPos;

    protected int $forwardPos;

    protected int $curLine;

    protected int $curCol;

    protected int $nextCurLine;

    protected int $nextCurCol;

    public function __construct(string $buffer)
    {
        $this->eofMarker = chr(1);
        $this->buffer = str_replace("\r\n", "\n", $buffer).$this->eofMarker;

        $this->reset();
    }

    public function reset(): void
    {
        $this->startPos = 0;

        $this->forwardPos = 0;
        $this->curLine = $this->curCol = 1;
        $this->nextCurLine = $this->nextCurCol = 1;
    }

    public function getEofMarker(): string
    {
        return $this->eofMarker;
    }

    public function getCurrentLine(): int
    {
        return $this->curLine;
    }

    public function getCurrentColumn(): int
    {
        return $this->curCol;
    }

    /**
     * Return the literal delimited by start and end position.
     */
    public function current(): string
    {
        return substr($this->buffer, $this->startPos, $this->forwardPos - $this->startPos);
    }

    public function currentChar(): string
    {
        return substr($this->buffer, $this->forwardPos, 1);
    }

    public function isEof(): bool
    {
        $currentChar = $this->currentChar();

        // substr after end of string returned false in PHP 5 and returns '' since PHP 7
        return in_array($currentChar, [$this->getEofMarker(), false, ''], true)
            || $this->startPos > strlen($this->buffer)
            || $this->forwardPos > strlen($this->buffer);
    }

    /**
     * Advance the forward position and return the literal delimited by start and end position.
     */
    public function forward(): string
    {
        if ($this->forwardPos < strlen($this->buffer)) {
            ++$this->forwardPos;
            ++$this->nextCurCol;
        }

        if ("\n" === $this->current()) {
            ++$this->nextCurLine;
            $this->nextCurCol = 1;
        }

        return $this->current();
    }

    public function forwardChar(): string
    {
        $this->forward();

        return $this->currentChar();
    }

    public function rewind(): void
    {
        $this->forwardPos = $this->startPos;
        $this->nextCurLine = $this->curLine;
        $this->nextCurCol = $this->curCol;
    }

    public function consume(): string
    {
        $current = $this->current();

        if ($current !== $this->getEofMarker()) {
            $this->startPos = $this->forwardPos;
        }

        $this->curLine = $this->nextCurLine;
        $this->curCol = $this->nextCurCol;

        return $current;
    }
}

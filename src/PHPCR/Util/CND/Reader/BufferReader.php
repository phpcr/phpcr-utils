<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class BufferReader implements ReaderInterface
{
    protected $eofMarker;

    protected $buffer;

    protected $startPos;

    protected $forwardPos;

    protected $curLine;

    protected $curCol;

    protected $nextCurLine;

    protected $nextCurCol;

    public function __construct($buffer)
    {
        $this->eofMarker = chr(1);
        $this->buffer = $buffer . $this->eofMarker;

        $this->reset();
    }

    public function reset()
    {
        $this->startPos = 0;

        $this->forwardPos = 0;
        $this->curLine = $this->curCol = 1;
        $this->nextCurLine = $this->nextCurCol = 1;
    }

    public function getEofMarker()
    {
        return $this->eofMarker;
    }

    /**
     * @return int
     */
    function getCurrentLine()
    {
        return $this->curLine;
    }

    /**
     * @return int
     */
    function getCurrentColumn()
    {
        return $this->curCol;
    }

    /**
     * Return the literal delimited by start and end position
     * @return string
     */
    public function current()
    {
        return substr($this->buffer, $this->startPos, $this->forwardPos - $this->startPos);
    }

    public function currentChar()
    {
        return substr($this->buffer, $this->forwardPos, 1);
    }

    public function isEof()
    {
        return $this->currentChar() === $this->getEofMarker()
            || $this->currentChar() === false
            || $this->startPos > strlen($this->buffer)
            || $this->forwardPos > strlen($this->buffer);
    }

    /**
     * Advance the forward position and return the literal delimited by start and end position
     * @return string
     */
    public function forward()
    {
        if ($this->forwardPos < strlen($this->buffer)) {
            $this->forwardPos++;
            $this->nextCurCol++;
        }

        if ($this->current() === PHP_EOL) {
            $this->nextCurLine++;
            $this->nextCurCol = 1;
        }

        return $this->current();
    }

    public function forwardChar()
    {
        $this->forward();
        return $this->currentChar();
    }

    public function rewind()
    {
        $this->forwardPos = $this->startPos;
        $this->nextCurLine = $this->curLine;
        $this->nextCurCol = $this->curCol;
    }

    public function consume()
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

<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class BufferReader implements ReaderInterface
{
    /**
     * @var string
     */
    protected $eofMarker;

    /**
     * @var string
     */
    protected $buffer;

    /**
     * @var int
     */
    protected $startPos;

    /**
     * @var int
     */
    protected $forwardPos;

    /**
     * @var int
     */
    protected $curLine;

    /**
     * @var int
     */
    protected $curCol;

    /**
     * @var int
     */
    protected $nextCurLine;

    /**
     * @var int
     */
    protected $nextCurCol;

    /**
     * @param string $buffer
     */
    public function __construct($buffer)
    {
        $this->eofMarker = chr(1);
        $this->buffer = str_replace("\r\n", "\n", $buffer) . $this->eofMarker;

        $this->reset();
    }

    public function reset()
    {
        $this->startPos = 0;

        $this->forwardPos = 0;
        $this->curLine = $this->curCol = 1;
        $this->nextCurLine = $this->nextCurCol = 1;
    }

    /**
     * @return string
     */
    public function getEofMarker()
    {
        return $this->eofMarker;
    }

    /**
     * @return int
     */
    public function getCurrentLine()
    {
        return $this->curLine;
    }

    /**
     * @return int
     */
    public function getCurrentColumn()
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

    /**
     * @return boolean
     */
    public function isEof()
    {
        return $this->currentChar() === $this->getEofMarker()
            || $this->currentChar() === false
            || $this->startPos > strlen($this->buffer)
            || $this->forwardPos > strlen($this->buffer);
    }

    /**
     * Advance the forward position and return the literal delimited by start and end position
     *
     * @return string
     */
    public function forward()
    {
        if ($this->forwardPos < strlen($this->buffer)) {
            $this->forwardPos++;
            $this->nextCurCol++;
        }

        if ($this->current() === "\n") {
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

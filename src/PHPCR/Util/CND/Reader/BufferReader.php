<?php

namespace PHPCR\Util\CND\Reader;

/**
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
    protected $eolMarker;

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
        $this->eolMarker = PHP_EOL;
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

    /**
     * @return string
     */
    public function getEofMarker()
    {
        return $this->eofMarker;
    }

    /**
     * @return string
     */
    public function getEolMarker()
    {
        return $this->eolMarker;
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

    /**
     * @return bool
     */
    public function isEof()
    {
        return $this->currentChar() === $this->getEofMarker()
            || $this->currentChar() === false
            || $this->startPos > strlen($this->buffer)
            || $this->forwardPos > strlen($this->buffer);
    }

    /**
     * @return bool
     */
    public function isEol()
    {
        $current = $this->current();
        $marker = $this->getEolMarker();

        $result = preg_match('#'. preg_quote($marker) .'$#', $current) === 1;
        return $result;
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

        if ($this->isEol()) {
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

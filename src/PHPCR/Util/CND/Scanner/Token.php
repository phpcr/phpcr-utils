<?php

namespace PHPCR\Util\CND\Scanner;

/**
 * Base Token class.
 *
 * Unless you want to redefine the token type constants, you should rather use GenericToken.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class Token
{
    /**
     * The type of token.
     */
    public int $type;

    /**
     * The token raw data.
     */
    public string $data;

    /**
     * The line where the token appears.
     */
    protected int $line;

    /**
     * The column where the token appears.
     */
    protected int $row;

    /**
     * Constructor.
     */
    public function __construct(int $type = 0, string $data = '', int $line = 0, int $row = 0)
    {
        $this->type = $type;
        $this->data = $data;
        $this->line = $line;
        $this->row = $row;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function __toString()
    {
        return sprintf("TOKEN(%s, '%s', %s, %s)", $this->type, trim($this->data), $this->line, $this->row);
    }

    public function setLine(int $line): void
    {
        $this->line = $line;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function setRow(int $row): void
    {
        $this->row = $row;
    }

    public function getRow(): int
    {
        return $this->row;
    }
}

<?php

declare(strict_types=1);

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
    public function __construct(
        private int $type = 0,
        /**
         * The token raw data.
         */
        private string $data = '',
        private int $line = 0,
        private int $row = 0
    ) {
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

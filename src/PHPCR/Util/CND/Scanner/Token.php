<?php

namespace PHPCR\Util\CND\Scanner;

/**
 * Base Token class.
 *
 * Unless you want to redefine the token type constants, you should rather use GenericToken.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class Token
{
    /**
     * The type of token
     *
     * @var int
     */
    public $type;

    /**
     * The token raw data
     *
     * @var string
     */
    public $data;

    /**
     * The line where the token appears
     *
     * @var int
     */
    protected $line;

    /**
     * The column where the token appears
     *
     * @var int
     */
    protected $row;

    /**
     * Constructor
     *
     * @param int    $type
     * @param string $data
     * @param int    $line
     * @param int    $row
     */
    public function __construct($type = 0, $data = '', $line = 0, $row = 0)
    {
        $this->type = $type;
        $this->data = $data;
        $this->line = $line;
        $this->row = $row;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        return sprintf("TOKEN(%s, '%s', %s, %s)", $this->type, trim($this->data), $this->line, $this->row);
    }

    /**
     * @param int $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param int $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }

    /**
     * @return int
     */
    public function getRow()
    {
        return $this->row;
    }

}

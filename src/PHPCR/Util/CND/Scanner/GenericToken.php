<?php

namespace PHPCR\Util\CND\Scanner;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class GenericToken extends Token
{
    public const TK_WHITESPACE = 0;
    public const TK_NEWLINE = 1;
    public const TK_STRING = 2;
    public const TK_COMMENT = 3;
    public const TK_IDENTIFIER = 4;
    public const TK_KEYWORD = 5;
    public const TK_SYMBOL = 6;
    public const TK_UNKNOWN = 99;

    public static function getTypeName($type)
    {
        switch ($type) {
            case self::TK_WHITESPACE: return 'Whitespace';
            case self::TK_NEWLINE: return 'Newline';
            case self::TK_STRING: return 'String';
            case self::TK_COMMENT: return 'Comment';
            case self::TK_IDENTIFIER: return 'Identifier';
            case self::TK_KEYWORD: return 'Keyword';
            case self::TK_SYMBOL: return 'Symbol';
        }

        return 'Unknown';
    }

    public function __toString()
    {
        return sprintf("TOKEN(%s, '%s', %s, %s)", self::getTypeName($this->getType()), trim($this->data), $this->line, $this->row);
    }
}

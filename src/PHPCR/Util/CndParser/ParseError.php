<?php

namespace PHPCR\Util\CndParser;

class ParseError extends \Exception
{
    public function __construct($message, $lineNo)
    {
        $message = '[line: '.$lineNo.'] '.$message;
        parent::__construct($message);
    }
}

<?php
namespace PHPCR\Util\QOM;


class NotSupportedOperandException extends \RuntimeException
{
    function __construct($constraint) {
        parent::__construct(get_class($constraint) . " is not supported by this query language");
    }
}
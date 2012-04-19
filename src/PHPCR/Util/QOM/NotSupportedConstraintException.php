<?php

namespace PHPCR\Util\QOM;

class NotSupportedConstraintException extends \RuntimeException
{
    public function __construct($constraint)
    {
        parent::__construct(get_class($constraint) . " is not supported by this query language");
    }
}

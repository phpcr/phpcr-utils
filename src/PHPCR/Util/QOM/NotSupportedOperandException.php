<?php

namespace PHPCR\Util\QOM;

/**
 * A helper exception to report not yet implemented functionality.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class NotSupportedOperandException extends \RuntimeException
{
    /**
     * Create the exception with an explaining message
     *
     * @param string $operand the constraint expression that is not supported
     */
    public function __construct($operand)
    {
        parent::__construct(get_class($operand) . " is not supported by this query language");
    }
}

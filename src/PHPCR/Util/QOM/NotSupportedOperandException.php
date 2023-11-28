<?php

declare(strict_types=1);

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
     * Create the exception with an explaining message.
     *
     * @param object $operand the constraint expression that is not supported
     */
    public static function fromOperand(object $operand): self
    {
        $class = $operand::class;

        return new self("$class is not supported by this query language");
    }
}

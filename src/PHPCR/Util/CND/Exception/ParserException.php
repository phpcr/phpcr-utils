<?php

namespace PHPCR\Util\CND\Exception;

use PHPCR\Util\CND\Scanner\TokenQueue;
use PHPCR\Util\CND\Scanner\GenericToken;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */
class ParserException extends \Exception
{
    public function __construct(TokenQueue $queue, $msg)
    {
        $token = $queue->peek();
        $msg = sprintf("PARSER ERROR: %s. Current token is [%s, '%s'] at line %s, column %s", $msg, GenericToken::getTypeName($token->getType()), $token->getData(), $token->getLine(), $token->getRow());

        // construct a lookup of the next tokens
        $lookup = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($queue->isEof()) {
                break;
            }
            $token = $queue->get();
            $lookup .= $token->getData() . ' ';
        }
        $msg .= "\nBuffer lookup: \"$lookup\"";

        parent::__construct($msg);
    }
}

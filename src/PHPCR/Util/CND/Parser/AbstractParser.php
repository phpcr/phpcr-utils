<?php

namespace PHPCR\Util\CND\Parser;

use PHPCR\Util\CND\Scanner\GenericToken as Token;
use PHPCR\Util\CND\Scanner\TokenQueue;
use PHPCR\Util\CND\Exception\ParserException;

/**
 * Abstract base class for parsers
 *
 * It implements helper functions for parsers:
 *
 *      - checkToken            - check if the next token matches
 *      - expectToken           - expect the next token to match
 *      - checkAndExpectToken   - check and then expect the next token to match
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
abstract class AbstractParser
{
    /**
     * The token queue
     * @var TokenQueue
     */
    protected $tokenQueue;

    /**
     * Check the next token without consuming it and return true if it matches the given type and data.
     * If the data is not provided (equal to null) then only the token type is checked.
     * Return false otherwise.
     *
     * @param int $type The expected token type
     * @param null|string $data The expected data or null
     *
     * @return bool
     */
    protected function checkToken($type, $data = null)
    {
        if ($this->tokenQueue->isEof()) {
            return false;
        }

        $token = $this->tokenQueue->peek();

        if ($token->getType() !== $type) {
            return false;
        }

        if ($data && $token->getData() !== $data) {
            return false;
        }

        return true;
    }

    /**
     * Check if the token data is one of the elements of the data array.
     *
     * @param int   $type
     * @param array $data
     *
     * @return bool
     */
    protected function checkTokenIn($type, array $data)
    {
        foreach ($data as $d) {
            if ($this->checkToken($type, $d)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the next token matches the expected type and data. If it does, then consume and return it,
     * otherwise throw an exception.
     *
     * @param int $type The expected token type
     * @param null|string $data The expected token data or null
     *
     * @return Token
     *
     * @throws ParserException
     */
    protected function expectToken($type, $data = null)
    {
        $token = $this->tokenQueue->peek();

        if (!$this->checkToken($type, $data)) {
            throw new ParserException($this->tokenQueue, sprintf("Expected token [%s, '%s']", Token::getTypeName($type), $data));
        }

        $this->tokenQueue->next();

        return $token;
    }

    /**
     * Check if the next token matches the expected type and data. If it does, then consume it, otherwise
     * return false.
     *
     * @param int $type The expected token type
     * @param null|string $data The expected token data or null
     * @return bool|Token
     */
    protected function checkAndExpectToken($type, $data = null)
    {
        if ($this->checkToken($type, $data)) {
            $token = $this->tokenQueue->peek();
            $this->tokenQueue->next();
            return $token;
        }

        return false;
    }
}

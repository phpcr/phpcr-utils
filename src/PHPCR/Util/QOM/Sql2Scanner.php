<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\InvalidQueryException;

/**
 * Split an SQL2 statement into string tokens. Allows lookup and fetching of tokens.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Sql2Scanner
{
    /**
     * The SQL2 query currently being parsed.
     *
     * @var string
     */
    protected $sql2;

    /**
     * Token scanning result of the SQL2 string.
     *
     * @var array
     */
    protected $tokens;

    /**
     * Parsing position in the SQL string.
     *
     * @var int
     */
    protected $curpos = 0;

    /**
     * Construct a scanner with the given SQL2 statement.
     *
     * @param string $sql2
     */
    public function __construct($sql2)
    {
        $this->sql2 = $sql2;
        $this->tokens = $this->scan($this->sql2);
    }

    /**
     * Get the next token without removing it from the queue.
     * Return an empty string when there are no more tokens.
     *
     * @param int $offset number of tokens to look ahead - defaults to 0, the current token
     *
     * @return string
     */
    public function lookupNextToken($offset = 0)
    {
        if ($this->curpos + $offset < count($this->tokens)) {
            return trim($this->tokens[$this->curpos + $offset]);
        }

        return '';
    }

    /**
     * Get the next token and remove it from the queue.
     * Return an empty string when there are no more tokens.
     *
     * @return string
     */
    public function fetchNextToken()
    {
        $token = $this->lookupNextToken();
        if ($token !== '') {
            $this->curpos += 1;
        }

        return trim($token);
    }

    /**
     * Expect the next token to be the given one and throw an exception if it's
     * not the case. The equality test is done case sensitively/insensitively
     * depending on the second parameter.
     *
     * @param string $token            The expected token
     * @param bool   $case_insensitive
     *
     * @throws InvalidQueryException
     */
    public function expectToken($token, $case_insensitive = true)
    {
        $nextToken = $this->fetchNextToken();
        if (!$this->tokenIs($nextToken, $token, $case_insensitive)) {
            throw new InvalidQueryException("Syntax error: Expected '$token', found '$nextToken' in {$this->sql2}");
        }
    }

    /**
     * Expect the next tokens to be the one given in the array of tokens and
     * throws an exception if it's not the case.
     *
     * @param array $tokens
     * @param bool  $case_insensitive
     *
     * @throws InvalidQueryException
     *
     * @see expectToken
     */
    public function expectTokens($tokens, $case_insensitive = true)
    {
        foreach ($tokens as $token) {
            $this->expectToken($token, $case_insensitive);
        }
    }

    /**
     * Test the equality of two tokens.
     *
     * @param string $token
     * @param string $value
     * @param bool   $case_insensitive
     *
     * @return bool
     */
    public function tokenIs($token, $value, $case_insensitive = true)
    {
        if ($case_insensitive) {
            $test = strtoupper($token) === strtoupper($value);
        } else {
            $test = $token === $value;
        }

        return $test;
    }

    /**
     * Scan a SQL2 string and extract the tokens.
     *
     * @param string $sql2
     *
     * @return array
     */
    protected function scan($sql2)
    {
        $tokens = [];
        $currentToken = '';
        $tokenEndChars = ['.', ',', '(', ')', '='];

        $stringStartCharacter = false;
        $isEscaped = false;
        $escapedQuotesCount = 0;
        $splitString = \str_split($sql2);
        for ($index = 0; $index < count($splitString); $index++) {
            $character = $splitString[$index];
            if (!$stringStartCharacter && in_array($character, [' ', "\t", "\n"], true)) {
                if ($currentToken !== '') {
                    $tokens[] = $currentToken;
                }
                $currentToken = '';
                continue;
            }
            if (!$stringStartCharacter && in_array($character, $tokenEndChars, true)) {
                if ($currentToken !== '') {
                    $tokens[] = $currentToken;
                }
                $tokens[] = $character;
                $currentToken = '';
                continue;
            }

            // Handling the squared brackets in queries
            if (!$isEscaped && $character === '[') {
                if ($currentToken !== '') {
                    $tokens[] = $currentToken;
                }
                $stringSize = $this->parseBrackets($sql2, $index);
                $tokens[] = substr($sql2, $index, $stringSize);
                // We need to subtract one here because the for loop will automatically increment the index
                $index += $stringSize - 1;
                continue;
            }

            $currentToken .= $character;

            if (!$isEscaped && in_array($character, ['"', "'"], true)) {
                // Checking if the previous or next value is a ' to handle the weird SQL strings
                // This will not check if the amount of quotes is even
                $nextCharacter = $this->getCharacterAtIndex($sql2, $index + 1);
                if ($character === "'" && $nextCharacter === "'") {
                    $isEscaped = true;
                    $escapedQuotesCount++;
                    continue;
                }
                // If the escaped quotes are not paired up. eg. "I'''m cool" would be a parsing error
                if ($escapedQuotesCount % 2 == 1 && $stringStartCharacter !== "'") {
                    throw new InvalidQueryException("Syntax error: Number of single quotes to be even: $currentToken");
                }
                if ($character === $stringStartCharacter) {
                    // reached the end of the string
                    $stringStartCharacter = false;
                    $tokens[] = $currentToken;
                    $currentToken = '';
                } elseif (!$stringStartCharacter) {
                    // If there is no start character already we have found the beginning of a new string
                    $stringStartCharacter = $character;

                    // When tokenizing `AS"abc"` add the current token (AS) as token already
                    if (strlen($currentToken) > 1) {
                        $tokens[] = substr($currentToken, 0, strlen($currentToken) - 1);
                        $currentToken = $character;
                    }
                }
            }
            $isEscaped = $character === '\\';
        }
        if ($currentToken !== '') {
            $tokens[] = $currentToken;
        }

        if ($stringStartCharacter) {
            throw new InvalidQueryException("Syntax error: unterminated quoted string $currentToken in '$sql2'");
        }

        return $tokens;
    }

    private function getCharacterAtIndex($string, $index)
    {
        if ($index < strlen($string)) {
            return $string[$index];
        }

        return '';
    }

    private function parseBrackets(string $query, int $index): int
    {
        $endPosition = strpos($query, ']', $index) + 1;

        return $endPosition - $index;
    }
}

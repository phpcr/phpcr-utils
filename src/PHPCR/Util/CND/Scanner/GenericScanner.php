<?php

namespace PHPCR\Util\CND\Scanner;

use PHPCR\Util\CND\Reader\ReaderInterface;
use PHPCR\Util\CND\Exception\ScannerException;

/**
 * Generic scanner detecting GenericTokens.
 *
 * This class can be extended and the class properties redefined in order to adapt
 * the token generation to your needs.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class GenericScanner extends AbstractScanner
{
    /**
     * Scan the given reader and construct a TokenQueue composed of GenericToken.
     *
     * @param ReaderInterface $reader
     *
     * @return TokenQueue
     */
    public function scan(ReaderInterface $reader)
    {
        $this->resetQueue();

        while (!$reader->isEof()) {
            $tokenFound = false;
            $tokenFound = $tokenFound || $this->consumeComments($reader);
            $tokenFound = $tokenFound || $this->consumeNewLine($reader);
            $tokenFound = $tokenFound || $this->consumeSpaces($reader);
            $tokenFound = $tokenFound || $this->consumeString($reader);
            $tokenFound = $tokenFound || $this->consumeIdentifiers($reader);
            $tokenFound = $tokenFound || $this->consumeSymbols($reader);

            if (!$tokenFound) {
                $char = $reader->forwardChar();
                $reader->consume();

                if ($char !== $reader->getEofMarker()) {
                    $token = new GenericToken(GenericToken::TK_UNKNOWN, $char);
                    $this->addToken($reader, $token);
                }
            }

        }

        return $this->getQueue();
    }

    /**
     * Detect and consume whitespaces
     *
     * @param ReaderInterface $reader
     *
     * @return boolean
     */
    protected function consumeSpaces(ReaderInterface $reader)
    {
        if (in_array($reader->currentChar(), $this->context->getWhitespaces())) {

            $char = $reader->forwardChar();
            while (in_array($char, $this->context->getWhitespaces())) {
                $char = $reader->forwardChar();
            }

            $buffer = $reader->consume();

            $token = new GenericToken(GenericToken::TK_WHITESPACE, $buffer);
            $this->addToken($reader, $token);

            return true;
        }

        return false;
    }

    /**
     * Detect and consume newlines
     *
     * @param ReaderInterface $reader
     *
     * @return boolean
     */
    protected function consumeNewLine(ReaderInterface $reader)
    {
        if ($reader->currentChar() === "\n") {

            $token = new GenericToken(GenericToken::TK_NEWLINE, "\n");
            $this->addToken($reader, $token);

            while ($reader->forward() === "\n") {
                $reader->consume();
                $reader->forward();
            }
            $reader->rewind();

            return true;
        }

        return false;
    }

    /**
     * Detect and consume strings
     *
     * @param ReaderInterface  $reader
     *
     * @return boolean
     *
     * @throws ScannerException
     */
    protected function consumeString(ReaderInterface $reader)
    {
        $curDelimiter = $reader->currentChar();
        if (in_array($curDelimiter, $this->context->getStringDelimiters())) {

            $char = $reader->forwardChar();
            while ($char !== $curDelimiter) {

                if ($char === "\n") {
                    throw new ScannerException($reader, "Newline detected in string");
                }

                $char = $reader->forwardChar();
            }
            $reader->forward();

            $token = new GenericToken(GenericToken::TK_STRING, $reader->consume());
            $this->addToken($reader, $token);

            return true;
        }

        return false;
    }

    /**
     * Detect and consume comments
     *
     * @param ReaderInterface $reader
     *
     * @return boolean
     */
    protected function consumeComments(ReaderInterface $reader)
    {
        if ($this->consumeBlockComments($reader)) {
            return true;
        }

        return $this->consumeLineComments($reader);
    }

    /**
     * Detect and consume block comments
     *
     * @param ReaderInterface  $reader
     *
     * @return boolean
     *
     * @throws ScannerException
     */
    protected function consumeBlockComments(ReaderInterface $reader)
    {
        $nextChar = $reader->currentChar();
        foreach ($this->context->getBlockCommentDelimiters() as $beginDelim => $endDelim) {

            if ($nextChar === $beginDelim[0]) {

                // Lookup the start delimiter
                for ($i = 1; $i <= strlen($beginDelim); $i++) {
                    $reader->forward();
                }
                if ($reader->current() === $beginDelim) {

                    // Start delimiter found, let's try to find the end delimiter
                    $nextChar = $reader->forwardChar();

                    while (! $reader->isEof()) {

                        if ($nextChar === $endDelim[0]) {

                            for ($i = 1; $i <= strlen($endDelim); $i++) {
                                $reader->forward();
                            }

                            if (substr($reader->current(), -2) === $endDelim) {
                                $token = new GenericToken(GenericToken::TK_COMMENT, $reader->consume());
                                $this->addToken($reader, $token);

                                return true;
                            }
                        }

                        $nextChar = $reader->forwardChar();
                    }

                    // End of file reached and no end delimiter found, error
                    throw new ScannerException($reader, "Unterminated block comment");

                } else {

                    // Start delimiter not found, rewind the looked up characters
                    $reader->rewind();

                    return false;
                }

            }

        }

        return false;

    }

    /**
     * Detect and consume line comments
     *
     * @param ReaderInterface $reader
     *
     * @return boolean
     */
    protected function consumeLineComments(ReaderInterface $reader)
    {
        $nextChar = $reader->currentChar();
        foreach ($this->context->getLineCommentDelimiters() as $delimiter) {

            if ($delimiter && $nextChar === $delimiter[0]) {

                for ($i = 1; $i <= strlen($delimiter); $i++) {
                    $reader->forward();
                }

                if ($reader->current() === $delimiter) {

                    // consume to end of line
                    $char = $reader->currentChar();
                    while (!$reader->isEof() && $char !== "\n") {
                        $char = $reader->forwardChar();
                    }
                    $token = new GenericToken(GenericToken::TK_COMMENT, $reader->consume());
                    $this->addToken($reader, $token);

                    return true;

                } else {

                    // Rewind the looked up characters
                    $reader->rewind();

                    return false;
                }

            }
        }

        return false;
    }

    /**
     * Detect and consume identifiers
     *
     * @param ReaderInterface $reader
     *
     * @return boolean
     */
    protected function consumeIdentifiers(ReaderInterface $reader)
    {
        $nextChar = $reader->currentChar();

        if (preg_match('/[a-zA-Z]/', $nextChar)) {
            $nextChar = $reader->forwardChar();
            while (preg_match('/[a-zA-Z0-9_]/', $nextChar)) {
                $nextChar = $reader->forwardChar();
            }
            $token = new GenericToken(GenericToken::TK_IDENTIFIER, $reader->consume());
            $this->addToken($reader, $token);

            return true;
        }

        return false;
    }

    /**
     * Detect and consume symbols
     *
     * @param ReaderInterface $reader
     *
     * @return boolean
     */
    protected function consumeSymbols(ReaderInterface $reader)
    {
        $found = false;
        $nextChar = $reader->currentChar();
        while (in_array($nextChar, $this->context->getSymbols())) {
            $found = true;
            $token = new GenericToken(GenericToken::TK_SYMBOL, $nextChar);
            $this->addToken($reader, $token);

            $reader->consume();
            $nextChar = $reader->forwardChar();
        }

        $reader->consume();

        return $found;
    }
}

<?php

namespace PHPCR\Util\CND\Scanner;

use PHPCR\Util\CND\Reader\ReaderInterface,
    PHPCR\Util\CND\Scanner\Token,
    PHPCR\Util\CND\Exception\ScannerException;

/**
 * Generic scanner detecting GenericTokens.
 *
 * This class can be extended and the class properties redefined in order to adapt
 * the token generation to your needs.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class GenericScanner extends AbstractScanner
{
    /**
     * Scan the given reader and construct a TokenQueue composed of GenericToken.
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return TokenQueue
     */
    public function scan(ReaderInterface $reader)
    {
        $this->debugSection("SCANNER CYCLE");

        $this->resetQueue();

        while (!$reader->isEof()) {

            $this->debug(sprintf('Loop on: [%s]', str_replace("\n", '<NL>', $reader->currentChar())));

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

        $this->debug('== EOF ==');

        return $this->getQueue();
    }

    /**
     * Detect and consume whitespaces
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeSpaces(ReaderInterface $reader)
    {
//        $this->debug('consumeSpaces');

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
    }

    /**
     * Detect and consume newlines
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeNewLine(ReaderInterface $reader)
    {
//        $this->debug('consumeNewline');

        if ($reader->currentChar() === PHP_EOL) {

            $token = new GenericToken(GenericToken::TK_NEWLINE, PHP_EOL);
            $this->addToken($reader, $token);


            while ($reader->forward() === PHP_EOL) {
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
     * @throws \LazyGuy\PhpParse\Exception\ScannerException
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeString(ReaderInterface $reader)
    {
//        $this->debug('consumeStrings');

        $curDelimiter = $reader->currentChar();
        if (in_array($curDelimiter, $this->context->getStringDelimiters())) {

            $char = $reader->forwardChar();
            while ($char !== $curDelimiter) {

                if ($char === PHP_EOL) {
                    throw new ScannerException($reader, "Newline detected in string");
                }

                $char = $reader->forwardChar();
            }
            $reader->forward();

            $token = new GenericToken(GenericToken::TK_STRING, $reader->consume());
            $this->addToken($reader, $token);
            return true;
        }
    }

    /**
     * Detect and consume comments
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
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
     * @throws \LazyGuy\PhpParse\Exception\ScannerException
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeBlockComments(ReaderInterface $reader)
    {
//        $this->debug('consumeBlockComments');

        $nextChar = $reader->currentChar();
        foreach($this->context->getBlockCommentDelimiters() as $beginDelim => $endDelim) {

            if (!$beginDelim || !$endDelim) {
                continue;
            }

            if ($nextChar === $beginDelim[0]) {

                // Lookup the start delimiter
                for ($i = 1; $i <= strlen($beginDelim); $i++) {
                    $reader->forward();
                }

                if ($reader->current() === $beginDelim) {

                    // Start delimiter found, let's try to find the end delimiter
                    $nextChar = $reader->forwardChar();
                    while ($nextChar !== $reader->getEofMarker()) {

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

    }

    /**
     * Detect and consume line comments
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeLineComments(ReaderInterface $reader)
    {
//        $this->debug('consumeLineComments');

        $nextChar = $reader->currentChar();
        foreach($this->context->getLineCommentDelimiters() as $delimiter) {

            if ($delimiter && $nextChar === $delimiter[0]) {

                for ($i = 1; $i <= strlen($delimiter); $i++) {
                    $reader->forward();
                }

                if ($reader->current() === $delimiter) {

                    // consume to end of line
                    $char = $reader->currentChar();
                    while (!$reader->isEof() && $char !== PHP_EOL) {
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
    }

    /**
     * Detect and consume identifiers
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeIdentifiers(ReaderInterface $reader)
    {
//        $this->debug('consumeIdentifiers');

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
    }

    /**
     * Detect and consume symbols
     *
     * @param \LazyGuy\PhpParse\Reader\ReaderInterface $reader
     * @return bool
     */
    protected function consumeSymbols(ReaderInterface $reader)
    {
//        $this->debug('consumeSymbols');

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

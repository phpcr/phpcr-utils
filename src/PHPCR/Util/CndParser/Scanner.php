<?php

namespace PHPCR\Util\CndParser;

class Scanner
{
    protected $lineNo = 0;
    protected $lines;
    protected $buffer = '';

    protected $tokens = array();
    protected $tokenPos = 0;
    protected $tokenCommitPos = 0;

    const RGX_UNQUOTED_STRING = '([a-zA-Z0-9:_/\.])';
    const RGX_SYMBOL = '([\(\)<>\[\-=\+\],])';
    const RGX_QUOTE = '(["\'])';
    const RGX_COMMENT_LINE = '&^\s*(//|/\*.*\*/).*$&';

    public function __construct($lines)
    {
        $this->lines = $lines;
        $this->tokens = $this->init();
    }

    public function init()
    {
        foreach ($this->lines as $i => $line) {
            $this->lineNo = $i;
            if (preg_match(self::RGX_COMMENT_LINE, $line)) {
                continue;
            }
            if (trim($line) == '') {
                continue;
            }

            $this->tokenize($line);
            $this->flushBuffer();
        }

        return $this->tokens;
    }

    protected function addToken($type, $value)
    {
        $this->tokens[] = array(
            'type' => $type, 
            'value' => $value, 
            'lineNo' => $this->lineNo
        );
    }

    protected function flushBuffer()
    {
        if ($this->buffer) {
            $this->addToken('string', $this->buffer);
            $this->buffer = '';
        }
    }

    protected function tokenize($line)
    {
        $stringQuoteOpen = false;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = substr($line, $i, 1);

            if (preg_match(self::RGX_QUOTE, $char)) {
                if ($stringQuoteOpen == $char) {
                    $this->flushBuffer();
                    $stringQuoteOpen = false;
                } elseif (false === $stringQuoteOpen) {
                    $stringQuoteOpen = $char;
                }

                continue;
            }

            if (preg_match(self::RGX_UNQUOTED_STRING, $char)) {
                $this->buffer .= $char;
            }

            if (false === $stringQuoteOpen && $char == ' ') {
                $this->flushBuffer();
            }

            if (false === $stringQuoteOpen && preg_match(self::RGX_SYMBOL, $char)) {
                $this->flushBuffer();
                $this->addToken('symbol', $char);
            }
        }
    }

    protected function getCurrentToken()
    {
        return $this->tokens[$this->tokenPos];
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function expect($type, $values = null, $optional = false)
    {
        if (!is_array($values)) {
            $values = array($values);
        }

        $ret = null;

        $t = $this->getCurrentToken();

        if ($type != $t['type'] && false === $optional) {
            if ($value) {
                $value = implode(',', $value);
                throw new ParseError(sprintf(
                    'Expected token of type "%s" with value "%s", got "%s" with value "%s"', 
                    $type, $value, $t['type'], $t['value']), $t['lineNo']
                );
            }
            throw new ParseError(sprintf('Expected token of type "%s", got "%s": %s', $type, $t['type'], $t['value']), $t['lineNo']);
        }

        if (null === $value && $type == $t['type']) {
            $ret = $t['value'];
        }

        foreach ($values as $value) {
            if ($t['type'] == $type && $t['value'] == $value) {
                $ret = $value;
            }
        }

        $this->inc();

        return $ret;
    }

    public function inc()
    {
        $this->tokenPos++;
    }

    public function commit()
    {
        $this->tokenCommitPos = $this->tokenPos;
    }

    public function reweindToLastCommit()
    {
        $this->tokenPos = $this->tokenCommitPos;
    }

    public function getValue()
    {
        $t = $this->getCurrentToken();
        $this->inc();
        return $t['value'];

    }
}

<?php

namespace PHPCR\Tests\Util\CND\Scanner;

use ArrayIterator;
use PHPCR\Util\CND\Reader\FileReader;
use PHPCR\Util\CND\Scanner\Context\DefaultScannerContext;
use PHPCR\Util\CND\Scanner\GenericScanner;
use PHPCR\Util\CND\Scanner\GenericToken as Token;
use PHPCR\Util\CND\Scanner\TokenFilter;
use PHPCR\Util\CND\Scanner\TokenQueue;
use PHPUnit\Framework\TestCase;
use Test;
use TestClass;

class GenericScannerTest extends TestCase
{
    protected $expectedTokens = [

        // <opening php tag>
        [Token::TK_SYMBOL, '<'],
        [Token::TK_SYMBOL, '?'],
        [Token::TK_IDENTIFIER, 'php'],
        [Token::TK_NEWLINE, ''],
        [Token::TK_NEWLINE, ''],

        // namespace Test\Foobar;
        [Token::TK_IDENTIFIER, 'namespace'],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_IDENTIFIER, Test::class],
        [Token::TK_SYMBOL, '\\'],
        [Token::TK_IDENTIFIER, 'Foobar'],
        [Token::TK_SYMBOL, ';'],
        [Token::TK_NEWLINE, ''],
        [Token::TK_NEWLINE, ''],

        // class TestClass {
        [Token::TK_IDENTIFIER, 'class'],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_IDENTIFIER, TestClass::class],
        [Token::TK_NEWLINE, ''],
        [Token::TK_SYMBOL, '{'],
        [Token::TK_NEWLINE, ''],

        // /** ... */
        [Token::TK_WHITESPACE, ''],
        [Token::TK_COMMENT, "/**\n     * Block comment.\n     */"],
        [Token::TK_NEWLINE, ''],

        // public function testMethod($testParam) {
        [Token::TK_WHITESPACE, ''],
        [Token::TK_IDENTIFIER, 'public'],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_IDENTIFIER, 'function'],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_IDENTIFIER, 'testMethod'],
        [Token::TK_SYMBOL, '('],
        [Token::TK_SYMBOL, '$'],
        [Token::TK_IDENTIFIER, 'testParam'],
        [Token::TK_SYMBOL, ')'],
        [Token::TK_NEWLINE, ''],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_SYMBOL, '{'],
        [Token::TK_NEWLINE, ''],

        // // Line comment
        [Token::TK_WHITESPACE, ''],
        [Token::TK_COMMENT, '// Line comment'],
        [Token::TK_NEWLINE, ''],

        // $string = 'This is a "Test // string"';
        [Token::TK_WHITESPACE, ''],
        [Token::TK_SYMBOL, '$'],
        [Token::TK_IDENTIFIER, 'string'],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_SYMBOL, '='],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_STRING, '\'This is a "Test // string"\''],
        [Token::TK_SYMBOL, ';'],
        [Token::TK_NEWLINE, ''],

        // empty line before return
        [Token::TK_NEWLINE, ''],

        // return "Test string";
        [Token::TK_WHITESPACE, ''],
        [Token::TK_IDENTIFIER, 'return'],
        [Token::TK_WHITESPACE, ''],
        [Token::TK_STRING, '\'Test string\''],
        [Token::TK_SYMBOL, ';'],
        [Token::TK_NEWLINE, ''],

        // }
        [Token::TK_WHITESPACE, ''],
        [Token::TK_SYMBOL, '}'],
        [Token::TK_NEWLINE, ''],
        [Token::TK_NEWLINE, ''],

        // // String in "comment"
        [Token::TK_WHITESPACE, ''],
        [Token::TK_COMMENT, '// String in "comment"'],
        [Token::TK_NEWLINE, ''],

        // }
        [Token::TK_SYMBOL, '}'],
        [Token::TK_NEWLINE, ''],
    ];

    protected $expectedTokensNoEmptyToken;

    public function setUp()
    {
        $this->expectedTokensNoEmptyToken = [];
        foreach ($this->expectedTokens as $token) {
            if ($token[0] !== Token::TK_NEWLINE && $token[0] !== Token::TK_WHITESPACE) {
                $this->expectedTokensNoEmptyToken[] = $token;
            }
        }
    }

    public function testScan()
    {
        $reader = new FileReader(__DIR__.'/../Fixtures/files/TestFile.php');

        // Test the raw file with newlines and whitespaces
        $scanner = new GenericScanner(new DefaultScannerContext());
        $queue = $scanner->scan($reader);
        $this->assertTokens($this->expectedTokens, $queue);
    }

    public function testFilteredScan()
    {
        $reader = new FileReader(__DIR__.'/../Fixtures/files/TestFile.php');

        // Test the raw file with newlines and whitespaces
        $context = new DefaultScannerContext();
        $context->addTokenFilter(new TokenFilter\NoNewlinesFilter());
        $context->addTokenFilter(new TokenFilter\NoWhitespacesFilter());
        $scanner = new GenericScanner($context);

        $queue = $scanner->scan($reader);
        $this->assertTokens($this->expectedTokensNoEmptyToken, $queue);
    }

    protected function assertTokens($tokens, TokenQueue $queue)
    {
        $queue->reset();

        $it = new ArrayIterator($tokens);

        $token = $queue->peek();

        while ($it->valid()) {
            $expectedToken = $it->current();

            $this->assertFalse($queue->isEof(), 'There is no more tokens, expected = '.$expectedToken[1]);

            $this->assertToken($expectedToken[0], $expectedToken[1], $token);

            $token = $queue->next();
            $it->next();
        }

        $this->assertTrue($queue->isEof(), 'There are more unexpected tokens.');
    }

    protected function assertToken($type, $data, Token $token)
    {
        $this->assertEquals($type, $token->getType(),
            sprintf('Expected token [%s, %s], found [%s, %s]', Token::getTypeName($type), $data, Token::getTypeName($token->getType()), $token->getData()));

        $this->assertEquals($data, trim($token->getData()),
            sprintf('Expected token [%s, %s], found [%s, %s]', Token::getTypeName($type), $data, Token::getTypeName($token->getType()), $token->getData()));
    }
}

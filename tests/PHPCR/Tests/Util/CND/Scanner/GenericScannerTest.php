<?php

namespace PHPCR\Tests\Util\CND\Scanner;

use PHPCR\Util\CND\Scanner\GenericScanner,
    PHPCR\Util\CND\Reader\FileReader,
    PHPCR\Util\CND\Scanner\GenericToken as Token,
    PHPCR\Util\CND\Scanner\TokenQueue,
    PHPCR\Util\CND\Scanner\TokenFilter,
    PHPCR\Util\CND\Scanner\Context\DefaultScannerContext;

class GenericScannerTest extends \PHPUnit_Framework_TestCase
{
    protected $expectedTokens = array(

        // <opening php tag>
        array(Token::TK_SYMBOL, '<'),
        array(Token::TK_SYMBOL, '?'),
        array(Token::TK_IDENTIFIER, 'php'),
        array(Token::TK_NEWLINE, ''),
        array(Token::TK_NEWLINE, ''),

        // namespace Test\Foobar;
        array(Token::TK_IDENTIFIER, 'namespace'),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_IDENTIFIER, 'Test'),
        array(Token::TK_SYMBOL, '\\'),
        array(Token::TK_IDENTIFIER, 'Foobar'),
        array(Token::TK_SYMBOL, ';'),
        array(Token::TK_NEWLINE, ''),
        array(Token::TK_NEWLINE, ''),

        // class TestClass {
        array(Token::TK_IDENTIFIER, 'class'),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_IDENTIFIER, 'TestClass'),
        array(Token::TK_NEWLINE, ''),
        array(Token::TK_SYMBOL, '{'),
        array(Token::TK_NEWLINE, ''),

        // /** ... */
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_COMMENT, "/**\n     * Block comment\n     */"),
        array(Token::TK_NEWLINE, ''),

        // public function testMethod($testParam) {
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_IDENTIFIER, 'public'),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_IDENTIFIER, 'function'),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_IDENTIFIER, 'testMethod'),
        array(Token::TK_SYMBOL, '('),
        array(Token::TK_SYMBOL, '$'),
        array(Token::TK_IDENTIFIER, 'testParam'),
        array(Token::TK_SYMBOL, ')'),
        array(Token::TK_NEWLINE, ''),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_SYMBOL, '{'),
        array(Token::TK_NEWLINE, ''),

        // // Line comment
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_COMMENT, '// Line comment'),
        array(Token::TK_NEWLINE, ''),

        // $string = 'This is a "Test // string"';
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_SYMBOL, '$'),
        array(Token::TK_IDENTIFIER, 'string'),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_SYMBOL, '='),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_STRING, '\'This is a "Test // string"\''),
        array(Token::TK_SYMBOL, ';'),
        array(Token::TK_NEWLINE, ''),

        // empty line before return
        array(Token::TK_NEWLINE, ''),

        // return "Test string";
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_IDENTIFIER, 'return'),
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_STRING, '"Test string"'),
        array(Token::TK_SYMBOL, ';'),
        array(Token::TK_NEWLINE, ''),

        // }
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_SYMBOL, '}'),
        array(Token::TK_NEWLINE, ''),
        array(Token::TK_NEWLINE, ''),

        // // String in "comment"
        array(Token::TK_WHITESPACE, ''),
        array(Token::TK_COMMENT, '// String in "comment"'),
        array(Token::TK_NEWLINE, ''),

        // }
        array(Token::TK_SYMBOL, '}'),
        array(Token::TK_NEWLINE, ''),
    );

    protected $expectedTokensNoEmptyToken;

    public function setUp()
    {
        $this->expectedTokensNoEmptyToken = array();
        foreach ($this->expectedTokens as $token) {
            if ($token[0] !== Token::TK_NEWLINE && $token[0] !== Token::TK_WHITESPACE) {
                $this->expectedTokensNoEmptyToken[] = $token;
            }
        }
    }

    public function testScan()
    {
        $reader = new FileReader(__DIR__ . '/../Fixtures/files/TestFile.php');

        // Test the raw file with newlines and whitespaces
        $scanner = new GenericScanner(new DefaultScannerContext());
        $queue = $scanner->scan($reader);
        $this->assertTokens($this->expectedTokens, $queue);
    }

    public function testFilteredScan()
    {
        $reader = new FileReader(__DIR__ . '/../Fixtures/files/TestFile.php');

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

        $it = new \ArrayIterator($tokens);

        $token = $queue->peek();

        while ($it->valid()) {

            $expectedToken = $it->current();

            $this->assertFalse($queue->isEof(), 'There is no more tokens, expected = ' . $expectedToken[1]);

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

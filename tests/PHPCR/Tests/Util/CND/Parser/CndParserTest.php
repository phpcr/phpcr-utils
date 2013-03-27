<?php

namespace PHPCR\Tests\Util\CND\Parser;

use PHPCR\Util\CND\Reader\FileReader,
    PHPCR\Util\CND\Parser\CndParser,
    PHPCR\Util\CND\Scanner\GenericScanner,
    PHPCR\Util\CND\Scanner\Context,
    PHPCR\Util\CND\Parser\SyntaxTreeNode;

class CndParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParseNormal()
    {
        $this->assertParsedFile(__DIR__ . '/../Fixtures/cnd/example.cnd', $this->expectedExampleTree);
    }

    public function testParseCompact()
    {
        $this->assertParsedFile(__DIR__ . '/../Fixtures/cnd/example.compact.cnd', $this->expectedExampleTree);
    }

    public function testParseVerbose()
    {
        $this->assertParsedFile(__DIR__ . '/../Fixtures/cnd/example.verbose.cnd', $this->expectedExampleTree);
    }

    public function testParseExample1()
    {
        // Assert there are no exceptions
        $this->parseFile(__DIR__ . '/../Fixtures/cnd/example1.cnd');

        $this->assertTrue(true); // To avoid the test being marked incomplete
        // TODO: write some real tests

    }

    public function testParseJackrabbitBuiltin()
    {
        $this->parseFile(__DIR__ . '/../Fixtures/cnd/jackrabbit-builtin-nodetypes.cnd');

        $this->assertTrue(true); // To avoid the test being marked incomplete
        // TODO: write some real tests
    }

    /**
     * Test the case where the parser did not parse correctly
     * the default values at the end of the parsed file.
     *
     * Assert no exception is thrown
     *
     * @return void
     */
    public function testNoStopAtEofError()
    {
        $this->parseFile(__DIR__ . '/../Fixtures/cnd/no-stop-at-eof.cnd');

        $this->assertTrue(true); // To avoid the test being marked incomplete
        // TODO: write some real tests
    }

    protected function parseFile($file)
    {
        $reader = new FileReader($file);
        $scanner = new GenericScanner(new Context\DefaultScannerContextWithoutSpacesAndComments());
        $queue = $scanner->scan($reader);

        //define('DEBUG', true);

        $parser = new CndParser($queue);
        return $parser->parse();
    }

}

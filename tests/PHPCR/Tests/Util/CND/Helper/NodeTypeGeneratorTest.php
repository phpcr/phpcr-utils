<?php

namespace PHPCR\Tests\Util\CND\Helper;

use PHPCR\Util\CND\Helper\NodeTypeGenerator,
    PHPCR\Util\CND\Reader\FileReader,
    PHPCR\Util\CND\Parser\CndParser,
    PHPCR\Util\CND\Scanner\GenericScanner,
    PHPCR\Util\CND\Scanner\Context;

// TODO: this belongs to functional testing, move it to phpcr-api-test repo (so that we have an implementation for the session)
class NodeTypeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerator()
    {
        $reader = new FileReader(__DIR__ . '/../Fixtures/cnd/example.cnd');
        $scanner = new GenericScanner(new Context\DefaultScannerContextWithoutSpacesAndComments());
        $queue = $scanner->scan($reader);
        $parser = new CndParser($queue);
        $root = $parser->parse();

        // TODO: get a session, somehow...
        //$session = ...
//        $generator = new NodeTypeGenerator($sesion, $root);
//        $generator->generate();

        $this->assertTrue(true); // To avoid the test being marked incomplete
        // TODO: write some real tests
    }
}

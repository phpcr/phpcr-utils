<?php

namespace PHPCR\Tests\Util\CndParser;

use PHPCR\Util\CndParser\CndParser;

class CndParserTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->parser = new CndParser;
    }

    public function testExample()
    {
        $example = file_get_contents(__DIR__.'/cnd/verbose.cnd');
        $lines = explode("\n", $example);
        $this->parser->parse($lines);
        die(print_r($this->parser->getScanner()->getTokens()));
        $this->assertTrue(true);
    }
}

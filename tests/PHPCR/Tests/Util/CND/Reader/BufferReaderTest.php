<?php

namespace PHPCR\Tests\Util\CND\Reader;

use PHPCR\Util\CND\Reader\BufferReader;

class BufferReaderTest extends \PHPUnit_Framework_TestCase
{
    public function test__construct()
    {
        $buffer = "Some random\nor\r\nstring";
        $reader = new BufferReader($buffer);

        $this->assertInstanceOf('\PHPCR\Util\CND\Reader\BufferReader', $reader);
        $this->assertAttributeEquals(str_replace("\r\n", "\n", $buffer) . $reader->getEofMarker(), 'buffer', $reader);
        $this->assertAttributeEquals(0, 'startPos', $reader);
        $this->assertAttributeEquals(0, 'forwardPos', $reader);

        $this->assertEquals(1, $reader->getCurrentLine());
        $this->assertEquals(1, $reader->getCurrentColumn());

        $this->assertEquals('', $reader->current());
        $this->assertEquals('S', $reader->forward());
        $this->assertEquals('So', $reader->forward());

        $reader->rewind();

        $this->assertEquals(1, $reader->getCurrentLine());
        $this->assertEquals(1, $reader->getCurrentColumn());

        $this->assertEquals('', $reader->current());
        $this->assertEquals('S', $reader->forward());
        $this->assertEquals('So', $reader->forward());
        $this->assertEquals('Som', $reader->forward());
        $this->assertEquals('Some', $reader->forward());
        $this->assertEquals('Some', $reader->consume());

        $this->assertEquals(5, $reader->getCurrentColumn());

        $this->assertEquals(' ', $reader->forward());
        $this->assertEquals(' r', $reader->forward());
        $reader->rewind();
        $this->assertEquals(' ', $reader->forward());
        $this->assertEquals(' ', $reader->consume());

        $this->assertEquals(6, $reader->getCurrentColumn());

        $this->assertEquals('r', $reader->forward());
        $this->assertEquals('ra', $reader->forward());
        $this->assertEquals('ran', $reader->forward());
        $this->assertEquals('rand', $reader->forward());
        $this->assertEquals('rando', $reader->forward());
        $this->assertEquals('random', $reader->forward());
        $this->assertEquals('random', $reader->consume());

        $this->assertEquals(12, $reader->getCurrentColumn());

        $this->assertEquals("\n", $reader->forward());
        $this->assertEquals("\n", $reader->consume());

        $this->assertEquals(2, $reader->getCurrentLine());
        $this->assertEquals(1, $reader->getCurrentColumn());
        $this->assertEquals('o', $reader->forward());
        $this->assertEquals('or', $reader->forward());
        $this->assertEquals('or', $reader->consume());
        $this->assertEquals("\n", $reader->forward());
        $this->assertEquals("\n", $reader->consume());

        $this->assertEquals(3, $reader->getCurrentLine());
        $this->assertEquals(1, $reader->getCurrentColumn());

        $this->assertEquals('s', $reader->forward());
        $this->assertEquals('st', $reader->forward());
        $this->assertEquals('str', $reader->forward());
        $this->assertEquals('stri', $reader->forward());
        $this->assertEquals('strin', $reader->forward());
        $this->assertEquals('string', $reader->forward());
        $this->assertEquals('string', $reader->consume());

        $this->assertEquals(3, $reader->getCurrentLine());
        $this->assertEquals(7, $reader->getCurrentColumn());

        $this->assertEquals($reader->getEofMarker(), $reader->forward());
        $this->assertEquals($reader->getEofMarker(), $reader->consume());
        $this->assertEquals($reader->getEofMarker(), $reader->forward());
    }

    public function test__constructEmptyString()
    {
        $reader = new BufferReader('');

        $this->assertInstanceOf('\PHPCR\Util\CND\Reader\BufferReader', $reader);
        $this->assertAttributeEquals($reader->getEofMarker(), 'buffer', $reader);
        $this->assertAttributeEquals(0, 'startPos', $reader);
        $this->assertAttributeEquals(0, 'forwardPos', $reader);

        $this->assertEquals(1, $reader->getCurrentLine());
        $this->assertEquals(1, $reader->getCurrentColumn());

        $this->assertEquals('', $reader->current());
        $this->assertEquals($reader->getEofMarker(), $reader->forward());
        $this->assertEquals($reader->getEofMarker(), $reader->forward());
        $reader->rewind();
        $this->assertEquals($reader->getEofMarker(), $reader->forward());
        $this->assertEquals($reader->getEofMarker(), $reader->consume());
    }

}

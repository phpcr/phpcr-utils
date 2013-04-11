<?php

namespace PHPCR\Tests\Util\CND\Reader;

use PHPCR\Util\CND\Reader\FileReader;

class FileReaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filepath = __DIR__ . '/../Fixtures/files/TestFile.txt';
        $this->reader = new FileReader($this->filepath);

        $this->lines = array(
            'This is a test file...',
            '',
            '...containing dummy content.',
            ''
        );

        $this->chars = array_merge(
            preg_split('//', $this->lines[0], -1, PREG_SPLIT_NO_EMPTY),
            array("\n", "\n"),
            preg_split('//', $this->lines[2], -1, PREG_SPLIT_NO_EMPTY),
            array("\n", "\n")
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test__construct_fileNotFound()
    {
        $reader = new FileReader('unexisting_file');
    }

    public function testGetPath()
    {
        $this->assertEquals($this->filepath, $this->reader->getPath());
    }

    public function testGetNextChar()
    {
        $curLine = 1;
        $curCol = 1;

        for ($i = 0; $i < count($this->chars); $i++) {

            $peek = $this->reader->currentChar();

            if ($peek === $this->reader->getEofMarker()) {
                $this->assertEquals(count($this->chars) - 1, $i);
                break;
            }

            $this->assertEquals($curLine, $this->reader->getCurrentLine());
            $this->assertEquals($curCol, $this->reader->getCurrentColumn());

            // Assert isEof is false before the end of the file
            $this->assertFalse($this->reader->isEof());

            // Assert isEol is true at end of the lines
            if ($peek === "\n") {
                $curLine++;
                $curCol = 1;
            } else {
                $curCol++;
            }

            // Assert the next character is the expected one
            $this->assertEquals($peek, $this->chars[$i]);
            $this->assertEquals(
                $this->chars[$i],
                $peek,
                sprintf("Character mismatch at position %s, expected '%s', found '%s'", $i, $this->chars[$i], $peek)
            );

            $this->reader->forward();
            $this->reader->consume();
        }

        // Check it's the end of the file
        $this->assertEquals($this->reader->getEofMarker(), $this->reader->currentChar());
        $this->assertTrue($this->reader->isEof());
        $this->assertEquals(false, $this->reader->forwardChar());
    }

}

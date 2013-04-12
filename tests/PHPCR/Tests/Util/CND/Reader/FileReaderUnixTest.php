<?php

namespace PHPCR\Tests\Util\CND\Reader;

use PHPCR\Util\CND\Reader\FileReader;

class FileReaderUnixTest extends \PHPUnit_Framework_TestCase
{
    const PHP_EOL = "\n";
    const FILEPATH = "../Fixtures/files/UnixTestFile.txt";

    /**
     * @var string
     */
    protected $filepath;

    /**
     * @var \PHPCR\Util\CND\Reader\FileReader
     */
    protected $reader;

    /**
     * @var array
     */
    protected $lines;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array
     */
    protected $chars;

    public function setUp()
    {
        $this->filepath = __DIR__ . '/' . self::FILEPATH;
        $this->reader = new FileReader($this->filepath);

        // swap the EOL marker with the one for the current platform being tested
        $reflection = new \ReflectionObject($this->reader);
        $property = $reflection->getProperty('eolMarker');
        $property->setAccessible(true);
        $property->setValue($this->reader, self::PHP_EOL); // forcing unix line ending as the file specified is using one

        $this->lines = array(
            'This is a test file...',
            '',
            '...containing dummy content.',
            ''
        );

        $this->content = file_get_contents($this->filepath);
        $this->chars = array_merge(
            preg_split('//', $this->lines[0], -1, PREG_SPLIT_NO_EMPTY),
            array(self::PHP_EOL, self::PHP_EOL),
            preg_split('//', $this->lines[2], -1, PREG_SPLIT_NO_EMPTY),
            array(self::PHP_EOL, self::PHP_EOL)
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

//            var_dump('Expected:' . $this->chars[$i] . ', found: ' . $peek);

            $this->assertEquals($curLine, $this->reader->getCurrentLine());
            $this->assertEquals($curCol, $this->reader->getCurrentColumn());

            // Assert isEof is false before the end of the file
            $this->assertFalse($this->reader->isEof());

            // Assert isEol is true at end of the lines
            if ($peek === self::PHP_EOL) {
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

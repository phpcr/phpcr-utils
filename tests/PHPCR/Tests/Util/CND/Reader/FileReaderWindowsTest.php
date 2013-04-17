<?php
namespace PHPCR\Tests\Util\CND\Reader;

require_once __DIR__ .'/FileReaderUnixTest.php';

class FileReaderWindowsTest extends FileReaderUnixTest
{
    const PHP_EOL = "\r\n";
    const FILEPATH = "../Fixtures/files/WindowsTestFile.txt";
}

<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql2Scanner;
use PHPUnit\Framework\TestCase;

class Sql2ScannerTest extends TestCase
{
    public function testToken()
    {
        $scanner = new Sql2Scanner('SELECT page.* FROM [nt:unstructured] AS page');
        $expected = [
            'SELECT',
            'page',
            '.',
            '*',
            'FROM',
            '[nt:unstructured]',
            'AS',
            'page',
        ];

        while ($token = $scanner->fetchNextToken()) {
            $this->assertEquals(array_shift($expected), $token);
        }
    }

    public function testDelimiter()
    {
        $scanner = new Sql2Scanner('SELECT page.* FROM [nt:unstructured] AS page');
        $expected = [
            '',
            ' ',
            '',
            '',
            ' ',
            ' ',
            ' ',
            ' ',
        ];

        while ($token = $scanner->fetchNextToken()) {
            $this->assertEquals(array_shift($expected), $scanner->getPreviousDelimiter());
        }
    }
}

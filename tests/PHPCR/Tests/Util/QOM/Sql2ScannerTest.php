<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Query\InvalidQueryException;
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
        $this->assertCount(0, $expected);
    }

    public function testStringTokenization()
    {
        $scanner = new Sql2Scanner('SELECT page.* FROM [nt:unstructured] AS page WHERE name ="Hello world"');
        $expected = [
            'SELECT',
            'page',
            '.',
            '*',
            'FROM',
            '[nt:unstructured]',
            'AS',
            'page',
            'WHERE',
            'name',
            '=',
            '"Hello world"',
        ];

        while ($token = $scanner->fetchNextToken()) {
            $this->assertEquals(array_shift($expected), $token);
        }
        $this->assertCount(0, $expected);
    }

    public function testStringTokenizationWithNewLines()
    {
        $scanner = new Sql2Scanner(<<<'SQL'
SELECT page.* 
FROM [nt:unstructured] AS page WHERE name ="Hello world"
SQL);
        $expected = [
            'SELECT',
            'page',
            '.',
            '*',
            'FROM',
            '[nt:unstructured]',
            'AS',
            'page',
            'WHERE',
            'name',
            '=',
            '"Hello world"',
        ];

        while ($token = $scanner->fetchNextToken()) {
            $this->assertEquals(array_shift($expected), $token);
        }
        $this->assertCount(0, $expected);
    }

    public function testThrowingErrorOnUnclosedString()
    {
        $this->expectException(InvalidQueryException::class);
        new Sql2Scanner('SELECT page.* FROM [nt:unstructured] AS page WHERE name ="Hello ');
    }
}

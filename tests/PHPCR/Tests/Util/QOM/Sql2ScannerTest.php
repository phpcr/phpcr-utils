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

        $this->expectTokensFromScanner($scanner, $expected);
    }

    /**
     * @dataProvider dataTestStringTokenization
     */
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

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function dataTestStringTokenization()
    {
        $multilineQuery = <<<'SQL'
SELECT page.* 
FROM [nt:unstructured] AS page 
WHERE name ="Hello world"
SQL;

        return [
            'single line query' => ['SELECT page.* FROM [nt:unstructured] AS page WHERE name ="Hello world"'],
            'multi line query' => [$multilineQuery],
        ];
    }

    public function testEscapingStrings()
    {
        $sql = <<<SQL
SELECT page.* FROM [nt:unstructured] AS page WHERE page.quotes = "\"'"
SQL;
        $scanner = new Sql2Scanner($sql);
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
            'page',
            '.',
            'quotes',
            '=',
            '"\"\'"',
        ];

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function testSQLEscapedStrings()
    {
        $sql = "WHERE page.name = 'Hello, it''s me.'";

        $scanner = new Sql2Scanner($sql);
        $expected = [
            'WHERE',
            'page',
            '.',
            'name',
            '=',
            "'Hello, it''s me.'",
        ];

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function testSQLEscapedStrings2()
    {
        $sql = "WHERE page.name = 'Hello, it''' AND";

        $scanner = new Sql2Scanner($sql);
        $expected = [
            'WHERE',
            'page',
            '.',
            'name',
            '=',
            "'Hello, it'''",
            'AND',
        ];

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function testThrowingErrorOnUnclosedString()
    {
        $this->expectException(InvalidQueryException::class);
        new Sql2Scanner('SELECT page.* FROM [nt:unstructured] AS page WHERE name ="Hello ');
    }

    /**
     * Function to assert that the tokens the scanner finds match the expected output
     * and the entire expected output is consumed.
     *
     * @param Sql2Scanner   $scanner
     * @param array<string> $expected
     */
    private function expectTokensFromScanner(Sql2Scanner $scanner, array $expected)
    {
        $actualTokens = [];
        while ($token = $scanner->fetchNextToken()) {
            $actualTokens[] = $token;
        }

        $this->assertEquals($expected, $actualTokens);
    }
}

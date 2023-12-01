<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Query\InvalidQueryException;
use PHPCR\Util\QOM\Sql2Scanner;
use PHPUnit\Framework\TestCase;

class Sql2ScannerTest extends TestCase
{
    public function testToken(): void
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
    public function testStringTokenization(string $query): void
    {
        $scanner = new Sql2Scanner($query);
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

    /**
     * @return array<string, string[]>
     */
    public function dataTestStringTokenization(): array
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

    public function testEscapingStrings(): void
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

    public function testSQLEscapedStrings(): void
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

    public function testSQLEscapedStrings2(): void
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

    public function testSquareBrackets(): void
    {
        $sql = 'WHERE ISSAMENODE(file, ["/home node"])';

        $scanner = new Sql2Scanner($sql);
        $expected = [
            'WHERE',
            'ISSAMENODE',
            '(',
            'file',
            ',',
            '[/home node]',
            ')',
        ];

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function testSquareBracketsWithoutQuotes(): void
    {
        $sql = 'WHERE ISSAMENODE(file, [/home node])';

        $scanner = new Sql2Scanner($sql);
        $expected = [
            'WHERE',
            'ISSAMENODE',
            '(',
            'file',
            ',',
            '[/home node]',
            ')',
        ];

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function testTokenizingWithMissingSpaces(): void
    {
        $sql = 'SELECT * AS"all"';

        $scanner = new Sql2Scanner($sql);
        $expected = [
            'SELECT',
            '*',
            'AS',
            '"all"',
        ];

        $this->expectTokensFromScanner($scanner, $expected);
    }

    public function testThrowingErrorOnUnclosedString(): void
    {
        $this->expectException(InvalidQueryException::class);
        new Sql2Scanner('SELECT page.* FROM [nt:unstructured] AS page WHERE name ="Hello ');
    }

    /**
     * Function to assert that the tokens the scanner finds match the expected output
     * and the entire expected output is consumed.
     *
     * @param array<string> $expected
     */
    private function expectTokensFromScanner(Sql2Scanner $scanner, array $expected): void
    {
        $actualTokens = [];
        while ($token = $scanner->fetchNextToken()) {
            $actualTokens[] = $token;
        }

        $this->assertEquals($expected, $actualTokens);
    }
}

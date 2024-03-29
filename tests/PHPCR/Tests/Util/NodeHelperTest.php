<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util;

use PHPCR\RepositoryException;
use PHPCR\Tests\Stubs\MockNode;
use PHPCR\Util\NodeHelper;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../Stubs/MockNode.php';

class NodeHelperTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    private array $namespaces = ['a' => 'http://phpcr', 'b' => 'http://jcr'];

    /**
     * @var string[]
     */
    private array $usedNames = ['a:x', 'b:y', 'c'];

    /**
     * @return array<array{0: string, 1: bool|string}>
     */
    public static function hints(): array
    {
        return [
            ['', true],
            [':', true],
            ['{}', true],
            ['b:', 'b:'],
            ['{http://jcr}', 'b:'],
            ['b:z', 'b:z'],
            ['{http://phpcr}bar', 'a:bar'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public static function invalidHints(): array
    {
        return [
            ['::'],
            ['a'], // no colon
            ['a:foo:'],
            ['{foo'],
            ['x:'], // not an existing namespace prefix
            ['{http://xy}'], // not an existing namespace uri
            ['x:a'], // not an existing namespace prefix with a local name prefix
            ['{http://xy}a'], // not an existing namespace uri with a local name prefix
        ];
    }

    public function testGenerateAutoNodeNameNoHint(): void
    {
        $result = NodeHelper::generateAutoNodeName($this->usedNames, $this->namespaces, 'a');
        $this->assertEquals('a:', substr($result, 0, 2));
    }

    /**
     * @dataProvider hints
     */
    public function testGenerateAutoNodeName(string $hint, bool|string $expect): void
    {
        $result = NodeHelper::generateAutoNodeName($this->usedNames, $this->namespaces, 'a', $hint);
        if (true === $expect) {
            $this->assertStringNotContainsString(':', $result);
        } else {
            $this->assertIsString($expect);
            $this->assertEquals($expect, substr($result, 0, strlen($expect)));
        }
    }

    /**
     * @dataProvider invalidHints
     */
    public function testGenerateAutoNodeNameInvalid(string $hint): void
    {
        $this->expectException(RepositoryException::class);
        NodeHelper::generateAutoNodeName($this->usedNames, $this->namespaces, 'a', $hint);
    }

    public function testIsSystemItem(): void
    {
        $sys = $this->createMock(MockNode::class);

        $sys->expects($this->once())
            ->method('getDepth')
            ->willReturn(0);

        $sys->expects($this->once())
            ->method('getName')
            ->willReturn('jcr:root');

        $this->assertTrue(NodeHelper::isSystemItem($sys));

        $sys = $this->createMock(MockNode::class);
        $sys->expects($this->once())
            ->method('getDepth')
            ->willReturn(1);

        $sys->expects($this->once())
            ->method('getName')
            ->willReturn('jcr:system');

        $this->assertTrue(NodeHelper::isSystemItem($sys));

        $top = $this->createMock(MockNode::class);
        $top->expects($this->once())
            ->method('getDepth')
            ->willReturn(1);

        $top->expects($this->once())
            ->method('getName')
            ->willReturn('jcrname'); // this is NOT in the jcr namespace

        $this->assertFalse(NodeHelper::isSystemItem($top));

        $deep = $this->createMock(MockNode::class);
        $deep->expects($this->once())
            ->method('getDepth')
            ->willReturn(2);

        $this->assertFalse(NodeHelper::isSystemItem($deep));
    }

    public function testCalculateOrderBeforeSwapLast(): void
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['one', 'two', 'four', 'three'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'three' => null,
            'two' => 'four', // TODO: this is an unnecessary but harmless NOOP. we should try to eliminate
        ];

        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeSwap(): void
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['one', 'four', 'three', 'two'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'three' => 'two',
            'two' => null,
        ];

        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeReverse(): void
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['four', 'three', 'two', 'one'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'three' => 'two',
            'two' => 'one',
            'one' => null,
        ];
        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeDeleted(): void
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['one', 'three', 'two'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'two' => null,
            'one' => 'three', // TODO: this is an unnecessary but harmless NOOP. we should try to eliminate
        ];

        $this->assertEquals($expected, $reorders);
    }

    /**
     * @group benchmark
     */
    public function testBenchmarkOrderBeforeArray(): void
    {
        $nodes = [];

        for ($i = 0; $i < 100000; ++$i) {
            $nodes[] = 'test'.$i;
        }

        $start = microtime(true);

        NodeHelper::orderBeforeArray('test250', 'test750', $nodes);

        $this->assertLessThan(1.0, microtime(true) - $start);
    }
}

<?php

namespace PHPCR\Tests\Util;

use PHPCR\Tests\Stubs\MockNode;
use PHPCR\Util\NodeHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

require_once __DIR__.'/../Stubs/MockNode.php';

class NodeHelperTest extends TestCase
{
    /**
     * @var array
     */
    private $namespaces = ['a' => 'http://phpcr', 'b' => 'http://jcr'];

    /**
     * @var array
     */
    private $usedNames = ['a:x', 'b:y', 'c'];

    /**
     * @return array
     */
    public static function hints()
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
     * @return array
     */
    public static function invalidHints()
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

    public function testGenerateAutoNodeNameNoHint()
    {
        $result = NodeHelper::generateAutoNodeName($this->usedNames, $this->namespaces, 'a');
        $this->assertEquals('a:', substr($result, 0, 2));
    }

    /**
     * @dataProvider hints
     */
    public function testGenerateAutoNodeName($hint, $expect)
    {
        $result = NodeHelper::generateAutoNodeName($this->usedNames, $this->namespaces, 'a', $hint);
        if (true === $expect) {
            $this->assertNotContains(':', $result);
        } else {
            $this->assertEquals($expect, substr($result, 0, strlen($expect)));
        }
    }

    /**
     * @dataProvider invalidHints
     * @expectedException \PHPCR\RepositoryException
     */
    public function testGenerateAutoNodeNameInvalid($hint)
    {
        NodeHelper::generateAutoNodeName($this->usedNames, $this->namespaces, 'a', $hint);
    }

    public function testIsSystemItem()
    {
        /** @var MockNode|PHPUnit_Framework_MockObject_MockObject $sys */
        $sys = $this->createMock(MockNode::class);

        $sys->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(0));

        $sys->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('jcr:root'));

        $this->assertTrue(NodeHelper::isSystemItem($sys));

        $sys = $this->createMock(MockNode::class);
        $sys->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(1));

        $sys->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('jcr:system'));

        $this->assertTrue(NodeHelper::isSystemItem($sys));

        /** @var MockNode|PHPUnit_Framework_MockObject_MockObject $top */
        $top = $this->createMock(MockNode::class);
        $top->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(1));

        $top->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('jcrname')) // this is NOT in the jcr namespace
;

        $this->assertFalse(NodeHelper::isSystemItem($top));

        /** @var MockNode|PHPUnit_Framework_MockObject_MockObject $deep */
        $deep = $this->createMock(MockNode::class);
        $deep->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(2));

        $this->assertFalse(NodeHelper::isSystemItem($deep));
    }

    public function testCalculateOrderBeforeSwapLast()
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['one', 'two', 'four', 'three'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'three' => null,
            'two'   => 'four', // TODO: this is an unnecessary but harmless NOOP. we should try to eliminate
        ];

        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeSwap()
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['one', 'four', 'three', 'two'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'three' => 'two',
            'two'   => null,
        ];

        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeReverse()
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['four', 'three', 'two', 'one'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'three' => 'two',
            'two'   => 'one',
            'one'   => null,
        ];
        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeDeleted()
    {
        $old = ['one', 'two', 'three', 'four'];
        $new = ['one', 'three', 'two'];

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = [
            'two'   => null,
            'one'   => 'three', // TODO: this is an unnecessary but harmless NOOP. we should try to eliminate
        ];

        $this->assertEquals($expected, $reorders);
    }

    /**
     * @group benchmark
     */
    public function testBenchmarkOrderBeforeArray()
    {
        $nodes = [];

        for ($i = 0; $i < 100000; $i++) {
            $nodes[] = 'test'.$i;
        }

        $start = microtime(true);

        NodeHelper::orderBeforeArray('test250', 'test750', $nodes);

        $this->assertLessThan(1.0, microtime(true) - $start);
    }
}

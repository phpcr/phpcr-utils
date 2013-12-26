<?php

namespace PHPCR\Tests\Util;

use PHPCR\Util\NodeHelper;

require_once(__DIR__.'/../Stubs/MockNode.php');

class NodeHelperTest extends \PHPUnit_Framework_TestCase
{
    private $namespaces = array('a' => 'http://phpcr', 'b' => 'http://jcr');
    private $usedNames = array('a:x', 'b:y', 'c');

    public static function hints()
    {
        return array(
            array('', true),
            array(':', true),
            array('{}', true),
            array('b:', 'b:'),
            array('{http://jcr}', 'b:'),
            array('b:z', 'b:z'),
            array('{http://phpcr}bar', 'a:bar'),
        );
    }

    public static function invalidHints()
    {
        return array(
            array('::'),
            array('a'), // no colon
            array('a:foo:'),
            array('{foo'),
            array('x:'), // not an existing namespace prefix
            array('{http://xy}'), // not an existing namespace uri
            array('x:a'), // not an existing namespace prefix with a local name prefix
            array('{http://xy}a'), // not an existing namespace uri with a local name prefix
        );
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
            $this->assertFalse(strpos($result, ':'));
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
        $sys = $this->getMock('PHPCR\Tests\Stubs\MockNode');
        $sys->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(0))
        ;
        $sys->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('jcr:root'))
        ;
        $this->assertTrue(NodeHelper::isSystemItem($sys));

        $sys = $this->getMock('PHPCR\Tests\Stubs\MockNode');
        $sys->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(1))
        ;
        $sys->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('jcr:system'))
        ;
        $this->assertTrue(NodeHelper::isSystemItem($sys));

        $top = $this->getMock('PHPCR\Tests\Stubs\MockNode');
        $top->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(1))
        ;
        $top->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('jcrname')) // this is NOT in the jcr namespace
        ;
        $this->assertFalse(NodeHelper::isSystemItem($top));

        $deep = $this->getMock('PHPCR\Tests\Stubs\MockNode');
        $deep->expects($this->once())
            ->method('getDepth')
            ->will($this->returnValue(2))
        ;
        $this->assertFalse(NodeHelper::isSystemItem($deep));
    }

    public function testCalculateOrderBeforeSwapLast()
    {
        $old = array('one', 'two', 'three', 'four');
        $new = array('one', 'two', 'four', 'three');

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = array(
            'three' => null,
            'two'   => 'four', // TODO: this is an unnecessary but harmless NOOP. we should try to eliminate
        );
        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeSwap()
    {
        $old = array('one', 'two', 'three', 'four');
        $new = array('one', 'four', 'three', 'two');

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = array(
            'three' => 'two',
            'two'   => null,
        );
        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeReverse()
    {
        $old = array('one', 'two', 'three', 'four');
        $new = array('four', 'three', 'two', 'one');

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = array(
            'three' => 'two',
            'two'   => 'one',
            'one'   => null,
        );
        $this->assertEquals($expected, $reorders);
    }

    public function testCalculateOrderBeforeDeleted()
    {
        $old = array('one', 'two', 'three', 'four');
        $new = array('one', 'three', 'two');

        $reorders = NodeHelper::calculateOrderBefore($old, $new);

        $expected = array(
            'two' => null,
            'one'   => 'three', // TODO: this is an unnecessary but harmless NOOP. we should try to eliminate
        );
        $this->assertEquals($expected, $reorders);
    }

    /**
     * @group benchmark
     */
    public function testBenchmarkOrderBeforeArray()
    {
        $nodes = array();

        for ($i = 0; $i < 1000000; $i++) {
            $nodes[] = 'test' . $i;
        }

        $start = microtime(true);

        NodeHelper::orderBeforeArray('test250', 'test750', $nodes);

        $this->assertLessThan(1.0, microtime(true) - $start);
    }
}

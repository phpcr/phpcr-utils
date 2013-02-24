<?php

namespace PHPCR\Tests\Util;

use PHPCR\Util\NodeHelper;

class NodeHelperTest extends \PHPUnit_Framework_TestCase
{
    private $namespaces = array('a' => 'http://phpcr', 'b' => 'http://jcr');
    private $usedNames = array('a:x', 'b:y', 'c');

    public static function hints() {
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

    public static function invalidHints() {
        return array(
            array('::'),
            array('a'), // no colon
            array('a:foo:'),
            array('{foo'),
            array('x'), // not an existing namespace
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

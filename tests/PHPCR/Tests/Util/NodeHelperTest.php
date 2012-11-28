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


}

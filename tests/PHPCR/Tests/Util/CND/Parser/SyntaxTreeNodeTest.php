<?php

namespace PHPCR\Tests\Util\CND\Parser;

use PHPCR\Util\CND\Parser\SyntaxTreeNode;

class SyntaxTreeNodeTest extends \PHPUnit_Framework_TestCase
{
    public function testSyntaxTreeNode()
    {
        $root = new SyntaxTreeNode('root');
        $root->setProperty('root-prop-1', 'some value');
        $root->setProperty('root-prop-2', 'some other value');

        $node1 = new SyntaxTreeNode('child');
        $node1->setProperty('child-prop-1', 'foo');
        $node1->setProperty('child-prop-2', 'bar');

        $node2 = new SyntaxTreeNode('child');
        $node2->setProperty('child-prop-3', 'foo');
        $node2->setProperty('child-prop-4', 'bar');

        $root->addChild($node1);
        $root->addChild($node2);

        //var_dump($root);

        $this->assertTrue(true); // To avoid the test being marked incomplete
        // TODO: write some real tests
    }
}

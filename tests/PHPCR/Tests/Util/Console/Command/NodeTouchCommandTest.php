<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeTouchCommand;

/**
 * Currently very minimal test for touch command
 */
class NodeTouchCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->command = new NodeTouchCommand;

        // override default concrete instance with mock
        $this->command->setPhpcrCliHelper($this->phpcrCliHelper);
        $this->application->add($this->command);
        $this->nodeType = $this->getMock('PHPCR\NodeType\NodeTypeInterface');
    }

    public function testTouch()
    {
        $node = $this->node1;

        $this->session->expects($this->exactly(2))
            ->method('getNode')
            ->will($this->returnCallback(function ($path) use ($node) {
                switch ($path) {
                    case '/':
                        return $node;
                    case '/cms':
                        return null;
                }
            }));

        $this->node1->expects($this->once())
            ->method('addNode');

        $this->session->expects($this->once())
            ->method('save');

        $ct = $this->executeCommand('phpcr:node:touch', array(
            'path' => '/cms',
        ));
    }

    public function testUpdate()
    {
        $this->session->expects($this->exactly(1))
            ->method('getNode')
            ->with('/cms')
            ->will($this->returnValue($this->node1));
        $this->node1->expects($this->once())
            ->method('getPrimaryNodeType')
            ->will($this->returnValue($this->nodeType));
        $this->nodeType->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('nt:unstructured'));

        $me = $this;
        $this->phpcrCliHelper->expects($this->once())
            ->method('processNode')
            ->will($this->returnCallback(function ($output, $node, $options) use ($me) {
                $me->assertEquals($me->node1, $node);
                $me->assertEquals(array(
                    'setProp' => array('foo=bar'),
                    'removeProp' => array('bar'),
                    'addMixins' => array('foo:bar'),
                    'removeMixins' => array('bar:foo'),
                    'dump' => true,
                ), $options);
            }));

        $ct = $this->executeCommand('phpcr:node:touch', array(
            'path' => '/cms',
            '--set-prop' => array('foo=bar'),
            '--remove-prop' => array('bar'),
            '--add-mixin' => array('foo:bar'),
            '--remove-mixin' => array('bar:foo'),
            '--dump' => true,
        ));
    }
}

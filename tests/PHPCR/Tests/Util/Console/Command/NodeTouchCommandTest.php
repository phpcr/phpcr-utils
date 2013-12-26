<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\PathNotFoundException;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeTouchCommand;

/**
 * Currently very minimal test for touch command
 */
class NodeTouchCommandTest extends BaseCommandTest
{
    /**
     * @var PhpcrHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    public $phpcrHelper;

    public function setUp()
    {
        parent::setUp();
        $command = new NodeTouchCommand;
        $this->application->add($command);

        // override default concrete instance with mock
        $this->phpcrHelper = $this->getMockBuilder('PHPCR\Util\Console\Helper\PhpcrHelper')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->phpcrHelper->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->session))
        ;
        $this->phpcrHelper->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('phpcr'))
        ;
        $this->helperSet->set($this->phpcrHelper);
    }

    public function testTouch()
    {
        $node = $this->node1;
        $child = $this->getMock('PHPCR\Tests\Stubs\MockNode');

        $this->session->expects($this->exactly(2))
            ->method('getNode')
            ->will($this->returnCallback(function ($path) use ($node) {
                switch ($path) {
                    case '/':
                        return $node;
                    case '/cms':
                        throw new PathNotFoundException();
                }
                throw new \Exception('Unexpected ' . $path);
            }));

        $this->node1->expects($this->once())
            ->method('addNode')
            ->with('cms')
            ->will($this->returnValue($child))
        ;

        $this->session->expects($this->once())
            ->method('save');

        $this->executeCommand('phpcr:node:touch', array(
            'path' => '/cms',
        ));
    }

    public function testUpdate()
    {
        $nodeType = $this->getMock('PHPCR\NodeType\NodeTypeInterface');
        $nodeType->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('nt:unstructured'))
        ;

        $this->session->expects($this->exactly(1))
            ->method('getNode')
            ->with('/cms')
            ->will($this->returnValue($this->node1))
        ;
        $this->node1->expects($this->once())
            ->method('getPrimaryNodeType')
            ->will($this->returnValue($nodeType))
        ;

        $me = $this;

        $this->phpcrHelper->expects($this->once())
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

        $this->executeCommand('phpcr:node:touch', array(
            'path' => '/cms',
            '--set-prop' => array('foo=bar'),
            '--remove-prop' => array('bar'),
            '--add-mixin' => array('foo:bar'),
            '--remove-mixin' => array('bar:foo'),
            '--dump' => true,
        ));
    }
}

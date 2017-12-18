<?php

namespace PHPCR\Tests\Util\Console\Command;

use Exception;
use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\PathNotFoundException;
use PHPCR\Tests\Stubs\MockNode;
use PHPCR\Util\Console\Command\NodeTouchCommand;
use PHPCR\Util\Console\Helper\PhpcrHelper;

/**
 * Currently very minimal test for touch command.
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

        $command = new NodeTouchCommand();
        $this->application->add($command);

        // Override default concrete instance with mock
        $this->phpcrHelper = $this->getMockBuilder(PhpcrHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->phpcrHelper->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->session));

        $this->phpcrHelper->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('phpcr'));

        $this->helperSet->set($this->phpcrHelper);
    }

    public function testTouch()
    {
        $node = $this->node1;
        $child = $this->createMock(MockNode::class);

        $this->session->expects($this->exactly(2))
            ->method('getNode')
            ->will($this->returnCallback(function ($path) use ($node) {
                switch ($path) {
                    case '/':
                        return $node;
                    case '/cms':
                        throw new PathNotFoundException();
                }

                throw new Exception('Unexpected '.$path);
            }));

        $this->node1->expects($this->once())
            ->method('addNode')
            ->with('cms')
            ->will($this->returnValue($child));

        $this->session->expects($this->once())
            ->method('save');

        $this->executeCommand('phpcr:node:touch', ['path' => '/cms']);
    }

    public function testUpdate()
    {
        $nodeType = $this->createMock(NodeTypeInterface::class);
        $nodeType->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('nt:unstructured'));

        $this->session->expects($this->exactly(1))
            ->method('getNode')
            ->with('/cms')
            ->will($this->returnValue($this->node1));

        $this->node1->expects($this->once())
            ->method('getPrimaryNodeType')
            ->will($this->returnValue($nodeType));

        $me = $this;

        $this->phpcrHelper->expects($this->once())
            ->method('processNode')
            ->will($this->returnCallback(function ($output, $node, $options) use ($me) {
                $me->assertEquals($me->node1, $node);
                $me->assertEquals([
                    'setProp'      => ['foo=bar'],
                    'removeProp'   => ['bar'],
                    'addMixins'    => ['foo:bar'],
                    'removeMixins' => ['bar:foo'],
                    'dump'         => true,
                ], $options);
            }));

        $this->executeCommand('phpcr:node:touch', [
            'path'           => '/cms',
            '--set-prop'     => ['foo=bar'],
            '--remove-prop'  => ['bar'],
            '--add-mixin'    => ['foo:bar'],
            '--remove-mixin' => ['bar:foo'],
            '--dump'         => true,
        ]);
    }
}

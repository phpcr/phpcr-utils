<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\PathNotFoundException;
use PHPCR\Tests\Stubs\MockNode;
use PHPCR\Util\Console\Command\NodeTouchCommand;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Currently very minimal test for touch command.
 */
class NodeTouchCommandTest extends BaseCommandTest
{
    /**
     * @var PhpcrHelper&MockObject
     */
    public $phpcrHelper;

    public function setUp(): void
    {
        parent::setUp();

        $command = new NodeTouchCommand();
        $this->application->add($command);

        // Override default concrete instance with mock
        $this->phpcrHelper = $this->getMockBuilder(PhpcrHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->phpcrHelper
            ->method('getSession')
            ->willReturn($this->session);

        $this->phpcrHelper
            ->method('getName')
            ->willReturn('phpcr');

        $this->helperSet->set($this->phpcrHelper);
    }

    public function testTouch(): void
    {
        $node = $this->node1;
        $child = $this->createMock(MockNode::class);

        $this->session->expects($this->exactly(2))
            ->method('getNode')
            ->willReturnCallback(function ($path) use ($node) {
                switch ($path) {
                    case '/':
                        return $node;
                    case '/cms':
                        throw new PathNotFoundException();
                }

                throw new \Exception('Unexpected '.$path);
            });

        $this->node1->expects($this->once())
            ->method('addNode')
            ->with('cms')
            ->willReturn($child);

        $this->session->expects($this->once())
            ->method('save');

        $this->executeCommand('phpcr:node:touch', ['path' => '/cms']);
    }

    public function testUpdate(): void
    {
        $nodeType = $this->createMock(NodeTypeInterface::class);
        $nodeType->expects($this->once())
            ->method('getName')
            ->willReturn('nt:unstructured');

        $this->session->expects($this->exactly(1))
            ->method('getNode')
            ->with('/cms')
            ->willReturn($this->node1);

        $this->node1->expects($this->once())
            ->method('getPrimaryNodeType')
            ->willReturn($nodeType);

        $me = $this;

        $this->phpcrHelper->expects($this->once())
            ->method('processNode')
            ->willReturnCallback(function ($output, $node, $options) use ($me): void {
                $me->assertEquals($me->node1, $node);
                $me->assertEquals([
                    'setProp' => ['foo=bar'],
                    'removeProp' => ['bar'],
                    'addMixins' => ['foo:bar'],
                    'removeMixins' => ['bar:foo'],
                    'dump' => true,
                ], $options);
            });

        $this->executeCommand('phpcr:node:touch', [
            'path' => '/cms',
            '--set-prop' => ['foo=bar'],
            '--remove-prop' => ['bar'],
            '--add-mixin' => ['foo:bar'],
            '--remove-mixin' => ['bar:foo'],
            '--dump' => true,
        ]);
    }
}

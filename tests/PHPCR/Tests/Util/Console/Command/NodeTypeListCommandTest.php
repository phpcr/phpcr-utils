<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Tests\Stubs\MockNodeTypeManager;
use PHPCR\Util\Console\Command\NodeTypeListCommand;
use PHPUnit\Framework\MockObject\MockObject;

class NodeTypeListCommandTest extends BaseCommandTest
{
    /**
     * @var MockNodeTypeManager&MockObject
     */
    private $nodeTypeManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new NodeTypeListCommand());
        $this->nodeTypeManager = $this->getMockBuilder(MockNodeTypeManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testNodeTypeList(): void
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace->expects($this->once())
            ->method('getNodeTypeManager')
            ->willReturn($this->nodeTypeManager);

        $this->nodeTypeManager->expects($this->once())
            ->method('getAllNodeTypes')
            ->willReturn([]);

        $this->executeCommand('phpcr:node-type:list', []);
    }
}

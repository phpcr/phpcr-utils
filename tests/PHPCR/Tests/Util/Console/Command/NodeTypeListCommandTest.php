<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Tests\Stubs\MockNodeTypeManager;
use PHPCR\Util\Console\Command\NodeTypeListCommand;
use PHPUnit_Framework_MockObject_MockObject;

class NodeTypeListCommandTest extends BaseCommandTest
{
    /**
     * @var MockNodeTypeManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $nodeTypeManager;

    public function setUp()
    {
        parent::setUp();

        $this->application->add(new NodeTypeListCommand());
        $this->nodeTypeManager = $this->getMockBuilder(MockNodeTypeManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testNodeTypeList()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));

        $this->workspace->expects($this->once())
            ->method('getNodeTypeManager')
            ->will($this->returnValue($this->nodeTypeManager));

        $this->nodeTypeManager->expects($this->once())
            ->method('getAllNodeTypes')
            ->will($this->returnValue([]));

        $this->executeCommand('phpcr:node-type:list', []);
    }
}

<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeTypeRegisterCommand;

class NodeTypeRegisterCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new NodeTypeRegisterCommand());
        $this->nodeTypeManager = $this->getMockBuilder(
            'PHPCR\Tests\Stubs\MockNodeTypeManager'
        )->disableOriginalConstructor()->getMock();
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
            ->method('registerNodeTypesCnd');

        $ct = $this->executeCommand('phpcr:node-type:register', array(
    'cnd-file' => __DIR__.'/fixtures/cnd_dummy.cnd',
        ));
    }
}

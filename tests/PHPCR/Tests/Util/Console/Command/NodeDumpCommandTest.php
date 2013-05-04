<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeDumpCommand;

class NodeDumpCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->treeWalker = $this->getMockBuilder(
            'PHPCR\Util\TreeWalker'
        )->disableOriginalConstructor()->getMock();
    }

    public function testCommand()
    {
        $this->dumperHelper->expects($this->once())
            ->method('getTreeWalker')
            ->will($this->returnValue($this->treeWalker));
        $this->session->expects($this->once())
            ->method('getNode')
            ->will($this->returnValue($this->node1));
        $this->node1->expects($this->any())
            ->method('getNodes')
            ->will($this->returnValue(array(
                $this->node1,
                $this->node2,
            )));

        $this->application->add(new NodeDumpCommand());

        $ct = $this->executeCommand('phpcr:node:dump', array());
    }
}

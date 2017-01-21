<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Util\Console\Command\WorkspacePurgeCommand;

class WorkspacePurgeCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();

        $this->application->add(new WorkspacePurgeCommand());
    }

    public function testNodeTypePurge()
    {
        $this->session->expects($this->once())
            ->method('getRootNode')
            ->will($this->returnValue($this->node1));

        $this->node1->expects($this->once())
            ->method('getProperties')
            ->will($this->returnValue([]));

        $this->node1->expects($this->once())
            ->method('getNodes')
            ->will($this->returnValue([]));

        $this->executeCommand('phpcr:workspace:purge', [
            '--force' => true,
        ]);
    }
}

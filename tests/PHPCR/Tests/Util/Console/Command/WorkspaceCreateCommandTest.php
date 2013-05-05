<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\WorkspaceCreateCommand;
use PHPCR\RepositoryInterface;

class WorkspaceCreateCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new WorkspaceCreateCommand());
    }

    public function testNodeTypeList()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));
        $this->workspace->expects($this->once())
            ->method('createWorkspace')
            ->with('test_workspace');
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->will($this->returnValue(true));

        $ct = $this->executeCommand('phpcr:workspace:create', array(
            'name' => 'test_workspace'
        ));
    }
}





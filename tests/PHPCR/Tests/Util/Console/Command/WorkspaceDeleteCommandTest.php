<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\WorkspaceDeleteCommand;
use PHPCR\RepositoryInterface;

class WorkspaceDeleteCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new WorkspaceDeleteCommand());
    }

    public function testDelete()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace))
        ;
        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->will($this->returnValue(array('default', 'test_workspace', 'other')))
        ;
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository))
        ;
        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->will($this->returnValue(true))
        ;
        $this->workspace->expects($this->once())
            ->method('deleteWorkspace')
            ->with('test_workspace')
        ;

        $ct = $this->executeCommand('phpcr:workspace:delete', array(
            'name' => 'test_workspace',
            '--force' => 'true',
        ));

        $this->assertContains("Deleted workspace 'test_workspace'.", $ct->getDisplay());
    }

    public function testDeleteNonexistent()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace))
        ;
        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->will($this->returnValue(array('default', 'other')))
        ;

        $ct = $this->executeCommand('phpcr:workspace:delete', array(
            'name' => 'test_workspace',
            '--force' => 'true',
        ));

        $this->assertContains("Workspace 'test_workspace' does not exist.", $ct->getDisplay());
    }
}

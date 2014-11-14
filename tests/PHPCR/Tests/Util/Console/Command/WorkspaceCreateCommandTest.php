<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\RepositoryException;
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

    public function testCreate()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace))
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
            ->method('createWorkspace')
            ->with('test_workspace')
        ;
        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->will($this->returnValue(array('default')))
        ;

        $this->executeCommand('phpcr:workspace:create', array(
            'name' => 'test_workspace'
        ));
    }

    /**
     * Handle trying to create existing workspace.
     */
    public function testCreateExisting()
    {
        $this->session->expects($this->exactly(2))
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace))
        ;
        $this->session->expects($this->exactly(2))
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
        $this->repository->expects($this->exactly(2))
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->will($this->returnValue(true))
        ;
        $this->workspace->expects($this->exactly(2))
            ->method('getAccessibleWorkspaceNames')
            ->will($this->returnValue(array('default', 'test')))
        ;

        $tester = $this->executeCommand(
            'phpcr:workspace:create',
            array('name' => 'test'),
            2
        );

        $this->assertContains('already has a workspace called "test"', $tester->getDisplay());

        $tester = $this->executeCommand(
            'phpcr:workspace:create',
            array(
                'name' => 'test',
                '--ignore-existing' => true
            ),
            0
        );

        $this->assertContains('already has a workspace called "test"', $tester->getDisplay());
    }
}

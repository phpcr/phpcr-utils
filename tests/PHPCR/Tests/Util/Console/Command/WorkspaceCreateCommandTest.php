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

        $this->executeCommand('phpcr:workspace:create', array(
            'name' => 'test_workspace'
        ));
    }

    /**
     * The real console catches this exception.
     *
     * @expectedException \PHPCR\RepositoryException
     * @expectedExceptionMessage Workspace exists
     */
    public function testCreateExisting()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace))
        ;
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->will($this->returnValue(true))
        ;
        $this->workspace->expects($this->once())
            ->method('createWorkspace')
            ->with('test_workspace')
            ->will($this->throwException(new RepositoryException('Workspace exists')))
        ;

        $this->executeCommand('phpcr:workspace:create', array(
            'name' => 'test_workspace'
        ));
    }
}

<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\RepositoryInterface;
use PHPCR\Util\Console\Command\WorkspaceCreateCommand;

class WorkspaceCreateCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new WorkspaceCreateCommand());
    }

    public function testCreate()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->session->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->willReturn(true);

        $this->workspace->expects($this->once())
            ->method('createWorkspace')
            ->with('test_workspace');

        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->willReturn(['default']);

        $this->executeCommand('phpcr:workspace:create', [
            'name' => 'test_workspace',
        ]);
    }

    /**
     * Handle trying to create existing workspace.
     */
    public function testCreateExisting()
    {
        $this->session->expects($this->exactly(2))
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->session->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->repository->expects($this->exactly(2))
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->willReturn(true);

        $this->workspace->expects($this->exactly(2))
            ->method('getAccessibleWorkspaceNames')
            ->willReturn(['default', 'test']);

        $tester = $this->executeCommand(
            'phpcr:workspace:create',
            ['name' => 'test'],
            2
        );

        $this->assertStringContainsString('already has a workspace called "test"', $tester->getDisplay());

        $tester = $this->executeCommand(
            'phpcr:workspace:create',
            [
                'name'              => 'test',
                '--ignore-existing' => true,
            ],
            0
        );

        $this->assertStringContainsString('already has a workspace called "test"', $tester->getDisplay());
    }
}

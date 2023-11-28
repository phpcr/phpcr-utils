<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\RepositoryInterface;
use PHPCR\Util\Console\Command\WorkspaceDeleteCommand;

class WorkspaceDeleteCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new WorkspaceDeleteCommand());
    }

    public function testDelete(): void
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->willReturn(['default', 'test_workspace', 'other']);

        $this->session->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)
            ->willReturn(true);

        $this->workspace->expects($this->once())
            ->method('deleteWorkspace')
            ->with('test_workspace');

        $ct = $this->executeCommand('phpcr:workspace:delete', [
            'name' => 'test_workspace',
            '--force' => 'true',
        ]);

        $this->assertStringContainsString("Deleted workspace 'test_workspace'.", $ct->getDisplay());
    }

    public function testDeleteNonexistent(): void
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->willReturn(['default', 'other']);

        $ct = $this->executeCommand('phpcr:workspace:delete', [
            'name' => 'test_workspace',
            '--force' => 'true',
        ]);

        $this->assertStringContainsString("Workspace 'test_workspace' does not exist.", $ct->getDisplay());
    }
}

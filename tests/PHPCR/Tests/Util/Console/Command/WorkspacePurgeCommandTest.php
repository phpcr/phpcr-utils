<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Util\Console\Command\WorkspacePurgeCommand;

class WorkspacePurgeCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new WorkspacePurgeCommand());
    }

    public function testNodeTypePurge(): void
    {
        $this->session->expects($this->once())
            ->method('getRootNode')
            ->willReturn($this->node1);

        $this->node1->expects($this->once())
            ->method('getProperties')
            ->willReturn([]);

        $this->node1->expects($this->once())
            ->method('getNodes')
            ->willReturn([]);

        $this->executeCommand('phpcr:workspace:purge', [
            '--force' => true,
        ]);
    }
}

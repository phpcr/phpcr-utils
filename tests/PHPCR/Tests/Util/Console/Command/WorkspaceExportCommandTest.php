<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\RepositoryInterface;
use PHPCR\Util\Console\Command\WorkspaceExportCommand;

class WorkspaceExportCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->addCommand(new WorkspaceExportCommand());
    }

    public function tearDown(): void
    {
        unlink('test');
    }

    public function testNodeTypeList(): void
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_EXPORT_SUPPORTED)
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('exportSystemView');

        $this->assertFileDoesNotExist('test', 'test export file must not exist, it will be overwritten');

        $ct = $this->executeCommand('phpcr:workspace:export', [
            'filename' => 'test',
        ]);

        $this->assertEquals(0, $ct->getStatusCode());
    }
}

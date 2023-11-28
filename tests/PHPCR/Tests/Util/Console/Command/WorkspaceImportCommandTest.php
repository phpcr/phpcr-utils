<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\ImportUUIDBehaviorInterface;
use PHPCR\RepositoryInterface;
use PHPCR\Util\Console\Command\WorkspaceImportCommand;

class WorkspaceImportCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new WorkspaceImportCommand());
    }

    public function testImport(): void
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('importXml')
            ->with('/', 'test_import.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW);

        $ct = $this->executeCommand('phpcr:workspace:import', [
            'filename' => 'test_import.xml',
        ]);

        $this->assertStringContainsString('Successfully imported', $ct->getDisplay());
    }

    public function testImportUuidBehaviorThrow(): void
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->willReturn($this->repository);

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)
            ->willReturn(true);

        $this->session->expects($this->once())
            ->method('importXml')
            ->with('/', 'test_import.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);

        $ct = $this->executeCommand('phpcr:workspace:import', [
            'filename' => 'test_import.xml',
            '--uuid-behavior' => 'throw',
        ]);

        $this->assertStringContainsString('Successfully imported', $ct->getDisplay());
    }
}

<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\ImportUUIDBehaviorInterface;
use PHPCR\RepositoryInterface;
use PHPCR\Util\Console\Command\WorkspaceImportCommand;

class WorkspaceImportCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();

        $this->application->add(new WorkspaceImportCommand());
    }

    public function testImport()
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)
            ->will($this->returnValue(true));

        $this->session->expects($this->once())
            ->method('importXml')
            ->with('/', 'test_import.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW);

        $ct = $this->executeCommand('phpcr:workspace:import', [
            'filename' => 'test_import.xml',
        ]);

        $this->assertContains('Successfully imported', $ct->getDisplay());
    }

    public function testImportUuidBehaviorThrow()
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)
            ->will($this->returnValue(true));

        $this->session->expects($this->once())
            ->method('importXml')
            ->with('/', 'test_import.xml', ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW);

        $ct = $this->executeCommand('phpcr:workspace:import', [
            'filename'        => 'test_import.xml',
            '--uuid-behavior' => 'throw',
        ]);

        $this->assertContains('Successfully imported', $ct->getDisplay());
    }
}

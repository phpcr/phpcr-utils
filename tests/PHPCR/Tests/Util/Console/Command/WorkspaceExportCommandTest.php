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

        $this->application->add(new WorkspaceExportCommand());
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

        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist('test', 'test export file must not exist, it will be overwritten');
        } else {
            // support phpunit 8 and older, can be removed when we only support php 9 or newer
            $this->assertFileNotExists('test', 'test export file must not exist, it will be overwritten');
        }

        $ct = $this->executeCommand('phpcr:workspace:export', [
            'filename' => 'test',
        ]);

        if (method_exists($ct, 'getStatusCode')) {
            // Only available since symfony 2.4
            $this->assertEquals(0, $ct->getStatusCode());
        }
        $this->assertFileExists('test');
    }
}

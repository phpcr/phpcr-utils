<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\WorkspaceImportCommand;
use PHPCR\RepositoryInterface;

class WorkspaceImportCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new WorkspaceImportCommand());
    }

    public function testNodeTypeList()
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)
            ->will($this->returnValue(true));
        $this->session->expects($this->once())
            ->method('importXml');

        $ct = $this->executeCommand('phpcr:workspace:import', array(
            'filename' => 'test_import.xml'
        ));

        $this->assertEmpty($ct->getDisplay());
    }
}

<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\WorkspaceExportCommand;
use PHPCR\RepositoryInterface;

class WorkspaceExportCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new WorkspaceExportCommand());
    }

    public function tearDown()
    {
        unlink('test');
    }

    public function testNodeTypeList()
    {
        $this->session->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));
        $this->repository->expects($this->once())
            ->method('getDescriptor')
            ->with(RepositoryInterface::OPTION_XML_EXPORT_SUPPORTED)
            ->will($this->returnValue(true));
        $this->session->expects($this->once())
            ->method('exportSystemView');

        $this->assertFileNotExists('test', 'test export file must not exist, it will be overwritten');

        $ct = $this->executeCommand('phpcr:workspace:export', array(
            'filename' => 'test'
        ));

        if (method_exists($ct, 'getStatusCode')) {
            // only available since symfony 2.4
            $this->assertEquals(0, $ct->getStatusCode());
        }
        $this->assertFileExists('test');
    }
}

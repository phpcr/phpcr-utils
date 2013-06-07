<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeDumpCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Helper\HelperSet;
use PHPCR\Util\Console\Helper\PhpcrHelper;

require_once(__DIR__.'/Stubb/MockNode.php');

abstract class BaseCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->session = $this->getMock('PHPCR\SessionInterface');
        $this->workspace = $this->getMock('PHPCR\WorkspaceInterface');
        $this->repository = $this->getMock('PHPCR\RepositoryInterface');

        $this->node1 = $this->getMock('PHPCR\Tests\Util\Console\Command\Stubb\MockNode');

        $this->dumperHelper = $this->getMockBuilder(
            'PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper'
        )->disableOriginalConstructor()->getMock();

        $this->helperSet = new HelperSet(array(
            'session' => new PhpcrHelper($this->session),
            'phpcr_console_dumper' => $this->dumperHelper,
        ));

        $this->session->expects($this->any())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));

        $this->workspace->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test'));

        $this->application = new Application();
        $this->application->setHelperSet($this->helperSet);
    }

    public function executeCommand($name, $args)
    {
        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $args = $args = array_merge(array(
            'command' => $command->getName(),
        ), $args);
        $commandTester->execute($args);



        return $commandTester;
    }
}

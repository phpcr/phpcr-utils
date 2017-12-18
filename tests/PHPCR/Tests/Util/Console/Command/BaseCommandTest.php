<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\NodeInterface;
use PHPCR\Query\QueryManagerInterface;
use PHPCR\Query\RowInterface;
use PHPCR\RepositoryInterface;
use PHPCR\SessionInterface;
use PHPCR\Tests\Stubs\MockNode;
use PHPCR\Tests\Stubs\MockRow;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use PHPCR\WorkspaceInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__.'/../../../Stubs/MockNode.php';
require_once __DIR__.'/../../../Stubs/MockNodeTypeManager.php';
require_once __DIR__.'/../../../Stubs/MockRow.php';

abstract class BaseCommandTest extends TestCase
{
    /**
     * @var SessionInterface|PHPUnit_Framework_MockObject_MockObject
     * */
    public $session;

    /**
     * @var WorkspaceInterface|PHPUnit_Framework_MockObject_MockObject
     */
    public $workspace;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    public $repository;

    /**
     * @var PhpcrConsoleDumperHelper|PHPUnit_Framework_MockObject_MockObject
     */
    public $dumperHelper;

    /**
     * @var NodeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    public $node1;

    /**
     * @var RowInterface|PHPUnit_Framework_MockObject_MockObject
     */
    public $row1;

    /**
     * @var QueryManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    public $queryManager;

    /**
     * @var HelperSet
     */
    public $helperSet;

    /**
     * @var Application
     */
    public $application;

    public function setUp()
    {
        $this->session = $this->createMock(SessionInterface::class);
        $this->workspace = $this->createMock(WorkspaceInterface::class);
        $this->repository = $this->createMock(RepositoryInterface::class);
        $this->queryManager = $this->createMock(QueryManagerInterface::class);

        $this->row1 = $this->createMock(MockRow::class);
        $this->node1 = $this->createMock(MockNode::class);

        $this->dumperHelper = $this->getMockBuilder(PhpcrConsoleDumperHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperSet = new HelperSet([
            'phpcr'                => new PhpcrHelper($this->session),
            'phpcr_console_dumper' => $this->dumperHelper,
        ]);

        $this->session->expects($this->any())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));

        $this->workspace->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test'));

        $this->workspace->expects($this->any())
            ->method('getQueryManager')
            ->will($this->returnValue($this->queryManager));

        $this->queryManager->expects($this->any())
            ->method('getSupportedQueryLanguages')
            ->will($this->returnValue(['JCR-SQL2']));

        $this->application = new Application();
        $this->application->setHelperSet($this->helperSet);
    }

    /**
     * Build and execute the command tester.
     *
     * @param string $name   command name
     * @param array  $args   command arguments
     * @param int    $status expected return status
     *
     * @return CommandTester
     */
    public function executeCommand($name, $args, $status = 0)
    {
        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $args = array_merge(['command' => $command->getName()], $args);
        $this->assertEquals($status, $commandTester->execute($args));

        return $commandTester;
    }
}

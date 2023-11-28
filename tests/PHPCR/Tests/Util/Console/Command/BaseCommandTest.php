<?php

declare(strict_types=1);

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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__.'/../../../Stubs/MockNode.php';
require_once __DIR__.'/../../../Stubs/MockNodeTypeManager.php';
require_once __DIR__.'/../../../Stubs/MockRow.php';

abstract class BaseCommandTest extends TestCase
{
    /**
     * @var SessionInterface|MockObject
     * */
    public $session;

    /**
     * @var WorkspaceInterface|MockObject
     */
    public $workspace;

    /**
     * @var RepositoryInterface|MockObject
     */
    public $repository;

    /**
     * @var PhpcrConsoleDumperHelper|MockObject
     */
    public $dumperHelper;

    /**
     * @var NodeInterface|MockObject
     */
    public $node1;

    /**
     * @var RowInterface|MockObject
     */
    public $row1;

    /**
     * @var QueryManagerInterface|MockObject
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

    public function setUp(): void
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
            'phpcr' => new PhpcrHelper($this->session),
            'phpcr_console_dumper' => $this->dumperHelper,
        ]);

        $this->session
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace
            ->method('getName')
            ->willReturn('test');

        $this->workspace
            ->method('getQueryManager')
            ->willReturn($this->queryManager);

        $this->queryManager
            ->method('getSupportedQueryLanguages')
            ->willReturn(['JCR-SQL2']);

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

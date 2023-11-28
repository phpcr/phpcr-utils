<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\ItemNotFoundException;
use PHPCR\Util\Console\Command\NodeDumpCommand;
use PHPCR\Util\TreeWalker;
use PHPCR\Util\UUIDHelper;
use PHPUnit\Framework\MockObject\MockObject;

class NodeDumpCommandTest extends BaseCommandTest
{
    /** @var TreeWalker|MockObject */
    protected $treeWalker;

    public function setUp(): void
    {
        parent::setUp();
        $this->treeWalker = $this->getMockBuilder(TreeWalker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ndCommand = new NodeDumpCommand();
        $this->application->add($ndCommand);
    }

    public function testCommand(): void
    {
        $this->dumperHelper
            ->expects($this->once())
            ->method('getTreeWalker')
            ->willReturn($this->treeWalker);

        $this->session
            ->expects($this->once())
            ->method('getNode')
            ->with('/')
            ->willReturn($this->node1);

        $this->treeWalker
            ->expects($this->once())
            ->method('traverse')
            ->with($this->node1);

        $this->executeCommand('phpcr:node:dump', []);
    }

    public function testCommandIdentifier(): void
    {
        $uuid = UUIDHelper::generateUUID();

        $this->dumperHelper
            ->expects($this->once())
            ->method('getTreeWalker')
            ->willReturn($this->treeWalker);

        $this->session
            ->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with($uuid)
            ->willReturn($this->node1);

        $this->treeWalker
            ->expects($this->once())
            ->method('traverse')
            ->with($this->node1);

        $this->executeCommand('phpcr:node:dump', ['identifier' => $uuid]);
    }

    public function testInvalidRefFormat(): void
    {
        $this->expectException(\Exception::class);

        $this->executeCommand('phpcr:node:dump', ['--ref-format' => 'xy']);
        $this->fail('invalid ref-format did not produce exception');
    }

    public function testNotFound(): void
    {
        $this->session
            ->expects($this->once())
            ->method('getNode')
            ->with('/')
            ->will($this->throwException(new ItemNotFoundException()));

        $ct = $this->executeCommand('phpcr:node:dump', [], 1);
        $this->assertStringContainsString('does not exist', $ct->getDisplay());
    }
}

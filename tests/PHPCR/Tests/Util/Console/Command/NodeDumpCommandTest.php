<?php

namespace PHPCR\Tests\Util\Console\Command;

use Exception;
use PHPCR\ItemNotFoundException;
use PHPCR\Util\Console\Command\NodeDumpCommand;
use PHPCR\Util\TreeWalker;
use PHPCR\Util\UUIDHelper;

class NodeDumpCommandTest extends BaseCommandTest
{
    /** @var TreeWalker|\PHPUnit_Framework_MockObject_MockObject */
    protected $treeWalker;

    public function setUp()
    {
        parent::setUp();
        $this->treeWalker = $this->getMockBuilder(TreeWalker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ndCommand = new NodeDumpCommand();
        $this->application->add($ndCommand);
    }

    public function testCommand()
    {
        $this->dumperHelper
            ->expects($this->once())
            ->method('getTreeWalker')
            ->will($this->returnValue($this->treeWalker));

        $this->session
            ->expects($this->once())
            ->method('getNode')
            ->with('/')
            ->will($this->returnValue($this->node1));

        $this->treeWalker
            ->expects($this->once())
            ->method('traverse')
            ->with($this->node1);

        $this->executeCommand('phpcr:node:dump', []);
    }

    public function testCommandIdentifier()
    {
        $uuid = UUIDHelper::generateUUID();

        $this->dumperHelper
            ->expects($this->once())
            ->method('getTreeWalker')
            ->will($this->returnValue($this->treeWalker));

        $this->session
            ->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with($uuid)
            ->will($this->returnValue($this->node1));

        $this->treeWalker
            ->expects($this->once())
            ->method('traverse')
            ->with($this->node1);

        $this->executeCommand('phpcr:node:dump', ['identifier' => $uuid]);
    }

    public function testInvalidRefFormat()
    {
        $this->expectException(Exception::class);

        $this->executeCommand('phpcr:node:dump', ['--ref-format' => 'xy']);
        $this->fail('invalid ref-format did not produce exception');
    }

    public function testNotFound()
    {
        $this->session
            ->expects($this->once())
            ->method('getNode')
            ->with('/')
            ->will($this->throwException(new ItemNotFoundException()));

        $ct = $this->executeCommand('phpcr:node:dump', [], 1);
        $this->assertContains('does not exist', $ct->getDisplay());
    }
}

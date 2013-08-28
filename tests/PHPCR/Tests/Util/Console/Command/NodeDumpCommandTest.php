<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\ItemNotFoundException;
use PHPCR\Util\UUIDHelper;
use Symfony\Component\Console\Application;

use PHPCR\Util\TreeWalker;
use PHPCR\Util\Console\Command\NodeDumpCommand;

class NodeDumpCommandTest extends BaseCommandTest
{
    /** @var TreeWalker|\PHPUnit_Framework_MockObject_MockObject */
    protected $treeWalker;

    public function setUp()
    {
        parent::setUp();
        $this->treeWalker = $this->getMockBuilder(
            'PHPCR\Util\TreeWalker'
        )->disableOriginalConstructor()->getMock();
    }

    protected function addCommand()
    {
        $ndCommand = new NodeDumpCommand();
        $ndCommand->setPhpcrConsoleDumperHelper($this->dumperHelper);
        $this->application->add($ndCommand);
    }

    public function testCommand()
    {
        $this->dumperHelper
            ->expects($this->once())
            ->method('getTreeWalker')
            ->will($this->returnValue($this->treeWalker))
        ;
        $this->session
            ->expects($this->once())
            ->method('getNode')
            ->with('/')
            ->will($this->returnValue($this->node1))
        ;
        $this->treeWalker
            ->expects($this->once())
            ->method('traverse')
            ->with($this->node1)
        ;

        $this->addCommand();
        $this->executeCommand('phpcr:node:dump', array());
    }

    public function testCommandIdentifier()
    {
        $uuid = UUIDHelper::generateUUID();

        $this->dumperHelper
            ->expects($this->once())
            ->method('getTreeWalker')
            ->will($this->returnValue($this->treeWalker))
        ;
        $this->session
            ->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with($uuid)
            ->will($this->returnValue($this->node1))
        ;
        $this->treeWalker
            ->expects($this->once())
            ->method('traverse')
            ->with($this->node1)
        ;

        $this->addCommand();
        $this->executeCommand('phpcr:node:dump', array('identifier' => $uuid));
    }

    public function testInvalidRefFormat()
    {
        $this->addCommand();

        try {
            $this->executeCommand('phpcr:node:dump', array('--ref-format' => 'xy'));
            $this->fail('invalid ref-format did not produce exception');
        } catch (\Exception $e) {
            // success
        }
    }

    public function testNotFound()
    {
        $this->session
            ->expects($this->once())
            ->method('getNode')
            ->with('/')
            ->will($this->throwException(new ItemNotFoundException()))
        ;

        $this->addCommand();

        $ct = $this->executeCommand('phpcr:node:dump', array(), 1);
        $this->assertContains('does not exist', $ct->getDisplay());
    }
}

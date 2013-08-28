<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\WorkspaceListCommand;

class WorkspaceListCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new WorkspaceListCommand());
    }

    public function testNodeTypeList()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));
        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->will($this->returnValue(array(
                'foo', 'bar'
            )));

        $ct = $this->executeCommand('phpcr:workspace:list', array(
        ));

        $expected = <<<HERE
The following 2 workspaces are available:
foo
bar

HERE;

        $this->assertEquals($expected, $ct->getDisplay());
    }
}

<?php

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Util\Console\Command\WorkspaceListCommand;

class WorkspaceListCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new WorkspaceListCommand());
    }

    public function testNodeTypeList()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace->expects($this->once())
            ->method('getAccessibleWorkspaceNames')
            ->willReturn(['foo', 'bar']);

        $ct = $this->executeCommand('phpcr:workspace:list', [
        ]);

        $expected = <<<'HERE'
The following 2 workspaces are available:
foo
bar

HERE;

        $this->assertEquals($expected, $ct->getDisplay());
    }
}

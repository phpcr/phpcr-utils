<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\WorkspaceQueryCommand;
use PHPCR\RepositoryInterface;

class WorkspaceQueryCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new WorkspaceQueryCommand());
        $this->queryManager = $this->getMock(
            'PHPCR\Query\QueryManagerInterface'
        );
        $this->query = $this->getMock('PHPCR\Query\QueryInterface');
    }

    public function testNodeTypeQuery()
    {
        $this->session->expects($this->once())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));
        $this->workspace->expects($this->once())
            ->method('getQueryManager')
            ->will($this->returnValue($this->queryManager));
        $this->queryManager->expects($this->once())
            ->method('createQuery')
            ->with('SELECT foo FROM foo', 'JCR-SQL2')
            ->will($this->returnValue($this->query));
        $this->query->expects($this->once())
            ->method('getLanguage')
            ->will($this->returnValue('FOOBAR'));
        $this->query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(array()));

        $ct = $this->executeCommand('phpcr:workspace:query', array(
            'query' => 'SELECT foo FROM foo',
        ));
    }
}

<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Query\QueryInterface;
use PHPCR\Util\Console\Command\WorkspaceQueryCommand;
use PHPUnit\Framework\MockObject\MockObject;

class WorkspaceQueryCommandTest extends BaseCommandTest
{
    /**
     * @var QueryInterface&MockObject
     */
    protected $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new WorkspaceQueryCommand());
        $this->query = $this->createMock(QueryInterface::class);
    }

    public function testQuery(): void
    {
        $this->queryManager
            ->method('getSupportedQueryLanguages')
            ->willReturn(['JCR-SQL2']);

        $this->session
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace
            ->method('getQueryManager')
            ->willReturn($this->queryManager);

        $this->queryManager->expects($this->once())
            ->method('createQuery')
            ->with('SELECT foo FROM foo', 'JCR-SQL2')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('getLanguage')
            ->willReturn('FOOBAR');

        $this->query->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $this->executeCommand('phpcr:workspace:query', [
            'query' => 'SELECT foo FROM foo',
        ]);
    }
}

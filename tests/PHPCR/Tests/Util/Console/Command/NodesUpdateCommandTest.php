<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Query\QueryInterface;
use PHPCR\Util\Console\Command\NodesUpdateCommand;
use PHPUnit\Framework\MockObject\MockObject;

class NodesUpdateCommandTest extends BaseCommandTest
{
    /**
     * @var QueryInterface&MockObject
     */
    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new NodesUpdateCommand());
        $this->query = $this->createMock(QueryInterface::class);
    }

    /**
     * @return array<array<array<string, mixed>>>
     */
    public function provideNodeUpdate(): array
    {
        return [
            // No query specified
            [[
                'exception' => \InvalidArgumentException::class,
            ]],
            // Specify query
            [[
                'query' => 'SELECT * FROM nt:unstructured WHERE foo="bar"',
            ]],
            // Set, remote properties and mixins
            [[
                'setProp' => [['foo', 'bar']],
                'removeProp' => ['bar'],
                'addMixin' => ['mixin1'],
                'removeMixin' => ['mixin1'],
                'query' => 'SELECT * FROM nt:unstructured',
            ]],
        ];
    }

    /**
     * @param array<string, string> $options
     */
    protected function setupQueryManager(array $options): void
    {
        $options = array_merge(['query' => ''], $options);

        $this->session
            ->method('getWorkspace')
            ->willReturn($this->workspace);

        $this->workspace
            ->method('getQueryManager')
            ->willReturn($this->queryManager);

        $this->queryManager
            ->method('createQuery')
            ->with($options['query'], 'JCR-SQL2')
            ->willReturn($this->query);

        $this->query
            ->method('execute')
            ->willReturn([$this->row1]);

        $this->row1
            ->method('getNode')
            ->willReturn($this->node1);
    }

    /**
     * @dataProvider provideNodeUpdate
     *
     * @param array<string, mixed> $options
     */
    public function testNodeUpdate(array $options): void
    {
        $options = array_merge([
            'query' => null,
            'setProp' => [],
            'removeProp' => [],
            'addMixin' => [],
            'removeMixin' => [],
            'exception' => null,
        ], $options);

        if ($options['exception']) {
            $this->expectException($options['exception']);
        }

        $this->setupQueryManager($options);

        $args = [
            '--query' => $options['query'],
            '--no-interaction' => true,
            '--set-prop' => [],
            '--remove-prop' => [],
            '--add-mixin' => [],
            '--remove-mixin' => [],
        ];

        $setPropertyArguments = [];
        foreach ($options['setProp'] as $setProp) {
            [$prop, $value] = $setProp;
            $setPropertyArguments[] = [$prop, $value, null];
            $args['--set-prop'][] = $prop.'='.$value;
        }

        foreach ($options['removeProp'] as $prop) {
            $setPropertyArguments[] = [$prop, null, null];
            $args['--remove-prop'][] = $prop;
        }

        $this->node1
            ->method('setProperty')
            ->withConsecutive(...$setPropertyArguments);

        foreach ($options['addMixin'] as $mixin) {
            $this->node1->expects($this->once())
                ->method('addMixin')
                ->with($mixin);

            $args['--add-mixin'][] = $mixin;
        }

        foreach ($options['removeMixin'] as $mixin) {
            $this->node1->expects($this->once())
                ->method('removeMixin')
                ->with($mixin);

            $args['--remove-mixin'][] = $mixin;
        }

        $this->executeCommand('phpcr:nodes:update', $args);
    }

    public function testApplyClosure(): void
    {
        $args = [
            '--query' => 'SELECT foo FROM bar',
            '--no-interaction' => true,
            '--apply-closure' => [
                '$session->getNodeByIdentifier("/foo"); $node->setProperty("foo", "bar");',
                function ($session, $node): void {
                    $node->setProperty('foo', 'bar');
                },
            ],
        ];

        $this->setupQueryManager(['query' => 'SELECT foo FROM bar']);

        $this->node1->expects($this->exactly(2))
            ->method('setProperty')
            ->with('foo', 'bar');

        $this->session->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with('/foo');

        $this->executeCommand('phpcr:nodes:update', $args);
    }
}

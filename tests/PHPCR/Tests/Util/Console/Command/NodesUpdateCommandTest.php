<?php

namespace PHPCR\Tests\Util\Console\Command;

use InvalidArgumentException;
use PHPCR\Query\QueryInterface;
use PHPCR\Util\Console\Command\NodesUpdateCommand;
use PHPUnit_Framework_MockObject_MockObject;

class NodesUpdateCommandTest extends BaseCommandTest
{
    /**
     * @var QueryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $query;

    public function setUp()
    {
        parent::setUp();

        $this->application->add(new NodesUpdateCommand());
        $this->query = $this->createMock(QueryInterface::class);
    }

    public function provideNodeUpdate()
    {
        return [
            // No query specified
            [['exception' => InvalidArgumentException::class]],
            // Specify query
            [['query' => 'SELECT * FROM nt:unstructured WHERE foo="bar"']],
            // Set, remote properties and mixins
            [[
                'setProp'     => [['foo', 'bar']],
                'removeProp'  => ['bar'],
                'addMixin'    => ['mixin1'],
                'removeMixin' => ['mixin1'],
                'query'       => 'SELECT * FROM nt:unstructured',
            ]],
        ];
    }

    protected function setupQueryManager($options)
    {
        $options = array_merge(['query' => ''], $options);

        $this->session->expects($this->any())
            ->method('getWorkspace')
            ->will($this->returnValue($this->workspace));

        $this->workspace->expects($this->any())
            ->method('getQueryManager')
            ->will($this->returnValue($this->queryManager));

        $this->queryManager->expects($this->any())
            ->method('createQuery')
            ->with($options['query'], 'JCR-SQL2')
            ->will($this->returnValue($this->query));

        $this->query->expects($this->any())
            ->method('execute')
            ->will($this->returnValue([
                $this->row1,
            ]));

        $this->row1->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue($this->node1));
    }

    /**
     * @dataProvider provideNodeUpdate
     */
    public function testNodeUpdate($options)
    {
        $options = array_merge([
            'query'       => null,
            'setProp'     => [],
            'removeProp'  => [],
            'addMixin'    => [],
            'removeMixin' => [],
            'exception'   => null,
        ], $options);

        if ($options['exception']) {
            $this->expectException($options['exception']);
        }

        $this->setupQueryManager($options);

        $args = [
            '--query'          => $options['query'],
            '--no-interaction' => true,
            '--set-prop'       => [],
            '--remove-prop'    => [],
            '--add-mixin'      => [],
            '--remove-mixin'   => [],
        ];

        foreach ($options['setProp'] as $setProp) {
            list($prop, $value) = $setProp;
            $this->node1->expects($this->at(0))
                ->method('setProperty')
                ->with($prop, $value);

            $args['--set-prop'][] = $prop.'='.$value;
        }

        foreach ($options['removeProp'] as $prop) {
            $this->node1->expects($this->at(1))
                ->method('setProperty')
                ->with($prop, null);

            $args['--remove-prop'][] = $prop;
        }

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

    public function testApplyClosure()
    {
        $args = [
            '--query'          => 'SELECT foo FROM bar',
            '--no-interaction' => true,
            '--apply-closure'  => [
                '$session->getNodeByIdentifier("/foo"); $node->setProperty("foo", "bar");',
                function ($session, $node) {
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

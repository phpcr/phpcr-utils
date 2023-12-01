<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Util\Console\Command\NodeMoveCommand;

class NodeMoveCommandTest extends BaseCommandTest
{
    /**
     * @return array<array<mixed[]>>
     */
    public function provideCommand(): array
    {
        return [
            [
                [
                    'source' => '/foo',
                    'destination' => '/bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideCommand
     *
     * @param array<mixed[]> $args
     */
    public function testCommand(array $args): void
    {
        $this->session->expects($this->once())
            ->method('move')
            ->with($args['source'], $args['destination']);

        $this->session->expects($this->once())
            ->method('save');

        $this->application->add(new NodeMoveCommand());
        $this->executeCommand('phpcr:node:move', $args);
    }
}

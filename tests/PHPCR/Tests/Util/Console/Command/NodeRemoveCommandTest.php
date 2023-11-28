<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Command;

use PHPCR\Util\Console\Command\NodeRemoveCommand;

class NodeRemoveCommandTest extends BaseCommandTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->application->add(new NodeRemoveCommand());
    }

    public function testRemove(): void
    {
        $this->session->expects($this->once())
            ->method('removeItem')
            ->with('/cms');

        $this->executeCommand('phpcr:node:remove', [
            '--force' => true,
            'path' => '/cms',
        ]);
    }

    public function testRemoveRoot(): void
    {
        $this->expectException(\LogicException::class);

        $this->executeCommand('phpcr:node:remove', [
            '--force' => true,
            'path' => '/',
        ]);
    }
}

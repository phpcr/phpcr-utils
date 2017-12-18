<?php

namespace PHPCR\Tests\Util\Console\Command;

use LogicException;
use PHPCR\Util\Console\Command\NodeRemoveCommand;

class NodeRemoveCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();

        $this->application->add(new NodeRemoveCommand());
    }

    public function testRemove()
    {
        $this->session->expects($this->once())
            ->method('removeItem')
            ->with('/cms');

        $this->executeCommand('phpcr:node:remove', [
            '--force' => true,
            'path'    => '/cms',
        ]);
    }

    public function testRemoveRoot()
    {
        $this->expectException(LogicException::class);

        $this->executeCommand('phpcr:node:remove', [
            '--force' => true,
            'path'    => '/',
        ]);
    }
}

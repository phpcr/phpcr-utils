<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeMoveCommand;

class NodeMoveCommandTest extends BaseCommandTest
{
    public function provideCommand()
    {
        return array(
            array(array('source' => '/foo', 'destination' => '/bar'))
        );
    }

    /**
     * @dataProvider provideCommand
     */
    public function testCommand($args)
    {
        $this->session->expects($this->once())
            ->method('move')
            ->with($args['source'], $args['destination']);
        $this->session->expects($this->once())
            ->method('save');

        $this->application->add(new NodeMoveCommand());
        $ct = $this->executeCommand('phpcr:node:move', $args);
    }
}

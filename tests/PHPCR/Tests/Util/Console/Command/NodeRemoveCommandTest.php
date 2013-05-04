<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
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

        $ct = $this->executeCommand('phpcr:node:remove', array(
            '--force' => true,
            'path' => '/cms',
        ));
    }

    /**
     * @expectedException \LogicException
     */
    public function testRemoveRoot()
    {
        $ct = $this->executeCommand('phpcr:node:remove', array(
            '--force' => true,
            'path' => '/',
        ));
    }
}

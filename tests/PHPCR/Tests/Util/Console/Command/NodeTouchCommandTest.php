<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodeTouchCommand;

/**
 * Currently very minimal test for touch command
 */
class NodeTouchCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new NodeTouchCommand());
        $this->nodeType = $this->getMock('PHPCR\NodeType\NodeTypeInterface');
    }

    public function testTouch()
    {
        $node = $this->node1;

        $this->session->expects($this->exactly(2))
            ->method('getNode')
            ->will($this->returnCallback(function ($path) use ($node) {
                switch ($path) {
                    case '/':
                        return $node;
                    case '/cms':
                        return null;
                }
            }));

        $this->node1->expects($this->once())
            ->method('addNode');

        $this->session->expects($this->once())
            ->method('save');

        $ct = $this->executeCommand('phpcr:node:touch', array(
            'path' => '/cms',
        ));
    }
}

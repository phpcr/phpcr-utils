<?php

namespace PHPCR\Tests\Util\Console\Command;

use Symfony\Component\Console\Application;
use PHPCR\Util\Console\Command\NodesUpdateCommand;

class NodesUpdateCommandTest extends BaseCommandTest
{
    public function setUp()
    {
        parent::setUp();
        $this->application->add(new NodesUpdateCommand());
        $this->query = $this->getMock('PHPCR\Query\QueryInterface');
    }

    public function provideNodeUpdate()
    {
        return array(

            // no query specified
            array(array(
                'exception' => 'InvalidArgumentException',
            )),

            // specify query
            array(array(
                'query' => 'SELECT * FROM nt:unstructured WHERE foo="bar"',
            )),

            // set, remote properties and mixins
            array(array(
                'setProp' => array(array('foo', 'bar')),
                'removeProp' => array('bar'),
                'addMixin' => array('mixin1'),
                'removeMixin' => array('mixin1'),
                'query' => 'SELECT * FROM nt:unstructured',
            )),
        );
    }

    protected function setupQueryManager($options)
    {
        $options = array_merge(array(
            'query' => '',
        ), $options);

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
            ->will($this->returnValue(array(
                $this->row1,
            )));
        $this->row1->expects($this->any())
            ->method('getNode')
            ->will($this->returnValue($this->node1));
    }

    /**
     * @dataProvider provideNodeUpdate
     */
    public function testNodeUpdate($options)
    {
        $options = array_merge(array(
            'query' => null,
            'setProp' => array(),
            'removeProp' => array(),
            'addMixin' => array(),
            'removeMixin' => array(),
            'exception' => null,
        ), $options);

        if ($options['exception']) {
            $this->setExpectedException($options['exception']);
        }

        $this->setupQueryManager($options);

        $args = array(
            '--query-language' => null,
            '--query' => $options['query'],
            '--no-interaction' => true,
            '--set-prop' => array(),
            '--remove-prop' => array(),
            '--add-mixin' => array(),
            '--remove-mixin' => array(),
        );

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

        $ct = $this->executeCommand('phpcr:nodes:update', $args);
    }

    public function testApplyClosure()
    {
        $args = array(
            '--query' => "SELECT foo FROM bar",
            '--no-interaction' => true,
            '--apply-closure' => array(
                '$session->getNodeByIdentifier("/foo"); $node->setProperty("foo", "bar");',
                function ($session, $node) {
                    $node->setProperty('foo', 'bar');
                }
            ),
        );

        $this->setupQueryManager(array('query' => 'SELECT foo FROM bar'));

        $this->node1->expects($this->exactly(2))
            ->method('setProperty')
            ->with('foo', 'bar');

        $this->session->expects($this->once())
            ->method('getNodeByIdentifier')
            ->with('/foo');

        $ct = $this->executeCommand('phpcr:nodes:update', $args);
    }
}

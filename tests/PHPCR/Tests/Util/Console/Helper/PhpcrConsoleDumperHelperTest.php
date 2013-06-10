<?php

use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;

class PhpcrConsoleDumperHelperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->helper = new PhpcrConsoleDumperHelper;
    }

    public function provideHelper()
    {
        return array(
            array(array()),
            array(array(
                'show_props' => true,
            )),
        );
    }

    /**
     * @dataProvider provideHelper
     */
    public function testGetTreeWalker($options)
    {
        $options = array_merge(array(
            'dump_uuids' => false,
            'ref_format' => 'uuid',
            'show_props' => false,
            'show_sys_nodes' => false,
        ), $options);

        $tw = $this->helper->getTreeWalker($this->output, $options);
        $this->assertInstanceOf(
            'PHPCR\Util\TreeWalker',
            $tw
        );

        $refl = new \ReflectionClass($tw);
        $propVisitorProp = $refl->getProperty('propertyVisitor');
        $propVisitorProp->setAccessible(true);
        $propVisitor = $propVisitorProp->getValue($tw);

        if ($options['show_props'] === true) {
            $this->assertInstanceOf(
                'PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor',
                $propVisitor
            );
        } else {
            $this->assertNull($propVisitor);
        }
    }
}

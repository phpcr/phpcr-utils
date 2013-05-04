<?php

use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;

class PhpcrConsoleDumperHelperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $this->helper = new PhpcrConsoleDumperHelper;
    }

    public function testHelper()
    {
        $nv = $this->helper->getNodeVisitor($this->output, array());
        $this->assertInstanceOf(
            'PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperNodeVisitor',
            $nv
        );

        $pv = $this->helper->getPropertyVisitor($this->output, array());
        $this->assertInstanceOf(
            'PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor',
            $pv
        );

        $tw = $this->helper->getTreeWalker($nv, $pv);
        $this->assertInstanceOf(
            'PHPCR\Util\TreeWalker',
            $tw
        );

        $snf = $this->helper->getSystemNodeFilter();
        $this->assertInstanceOf(
            'PHPCR\Util\Console\Helper\TreeDumper\SystemNodeFilter',
            $snf
        );
    }
}

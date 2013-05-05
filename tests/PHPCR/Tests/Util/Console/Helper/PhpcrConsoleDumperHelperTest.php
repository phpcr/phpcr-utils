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
        $tw = $this->helper->getTreeWalker($this->output, array());
        $this->assertInstanceOf(
            'PHPCR\Util\TreeWalker',
            $tw
        );
    }
}

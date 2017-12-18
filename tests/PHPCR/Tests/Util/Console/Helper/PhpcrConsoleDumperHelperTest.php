<?php

use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPCR\Util\TreeWalker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class PhpcrConsoleDumperHelperTest extends TestCase
{
    private $outputMock;
    /**
     * @var PhpcrConsoleDumperHelper
     */
    private $helper;

    public function setUp()
    {
        $this->outputMock = $this->createMock(OutputInterface::class);
        $this->helper = new PhpcrConsoleDumperHelper();
    }

    public function provideHelper()
    {
        return [[[]], [['show_props' => true]]];
    }

    /**
     * @dataProvider provideHelper
     */
    public function testGetTreeWalker($options)
    {
        $options = array_merge([
            'dump_uuids'     => false,
            'ref_format'     => 'uuid',
            'show_props'     => false,
            'show_sys_nodes' => false,
        ], $options);

        $tw = $this->helper->getTreeWalker($this->outputMock, $options);
        $this->assertInstanceOf(TreeWalker::class, $tw);

        $reflection = new ReflectionClass($tw);
        $propVisitorProp = $reflection->getProperty('propertyVisitor');
        $propVisitorProp->setAccessible(true);
        $propVisitor = $propVisitorProp->getValue($tw);

        if ($options['show_props'] === true) {
            $this->assertInstanceOf(ConsoleDumperPropertyVisitor::class, $propVisitor);
        } else {
            $this->assertNull($propVisitor);
        }
    }
}

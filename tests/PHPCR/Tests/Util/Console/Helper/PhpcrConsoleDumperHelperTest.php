<?php

declare(strict_types=1);

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

    public function setUp(): void
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
    public function testGetTreeWalker($options): void
    {
        $options = array_merge([
            'dump_uuids' => false,
            'ref_format' => 'uuid',
            'show_props' => false,
            'show_sys_nodes' => false,
        ], $options);

        $tw = $this->helper->getTreeWalker($this->outputMock, $options);
        $this->assertInstanceOf(TreeWalker::class, $tw);

        $reflection = new ReflectionClass($tw);
        $propVisitorProp = $reflection->getProperty('propertyVisitor');
        $propVisitorProp->setAccessible(true);
        $propVisitor = $propVisitorProp->getValue($tw);

        if (true === $options['show_props']) {
            $this->assertInstanceOf(ConsoleDumperPropertyVisitor::class, $propVisitor);
        } else {
            $this->assertNull($propVisitor);
        }
    }
}

<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\Console\Helper;

use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class PhpcrConsoleDumperHelperTest extends TestCase
{
    /**
     * @var MockObject&OutputInterface
     */
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

    /**
     * @return array<array<array<string, bool>>>
     */
    public function provideHelper(): array
    {
        return [
            [[]],
            [['show_props' => true]],
        ];
    }

    /**
     * @dataProvider provideHelper
     *
     * @param array<string, bool> $options
     */
    public function testGetTreeWalker(array $options): void
    {
        $options = array_merge([
            'dump_uuids' => false,
            'ref_format' => 'uuid',
            'show_props' => false,
            'show_sys_nodes' => false,
        ], $options);

        $tw = $this->helper->getTreeWalker($this->outputMock, $options);

        $reflection = new \ReflectionClass($tw);
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

<?php

namespace PHPCR\Util\Console\Helper\TreeDumper;

use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\ItemVisitorInterface;

/**
 * TODO: this should base on the TraversingItemVisitor
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
abstract class ConsoleDumperItemVisitor implements ItemVisitorInterface
{
    /**
     * Target for printing information
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Current depth in the tree
     *
     * @var int
     */
    protected $level = 0;

    /**
     * Instantiate the console dumper visitor
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the current depth level for indention
     *
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }
}

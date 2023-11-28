<?php

declare(strict_types=1);

namespace PHPCR\Util\Console\Helper\TreeDumper;

use PHPCR\ItemVisitorInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO: this should base on the TraversingItemVisitor.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
abstract class ConsoleDumperItemVisitor implements ItemVisitorInterface
{
    /**
     * Target for printing information.
     */
    protected OutputInterface $output;

    /**
     * Current depth in the tree.
     */
    protected int $level = 0;

    /**
     * Instantiate the console dumper visitor.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Set the current depth level for indention.
     */
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
}

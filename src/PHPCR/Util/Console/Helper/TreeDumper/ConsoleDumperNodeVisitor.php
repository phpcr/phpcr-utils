<?php

declare(strict_types=1);

namespace PHPCR\Util\Console\Helper\TreeDumper;

use PHPCR\ItemInterface;
use PHPCR\NodeInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO: this should base on the TraversingItemVisitor.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ConsoleDumperNodeVisitor extends ConsoleDumperItemVisitor
{
    /**
     * Whether to print the UUIDs or not.
     */
    protected bool $identifiers;

    /**
     * Show the full path for the node.
     */
    protected bool $showFullPath = false;

    /**
     * @param bool $identifiers whether to output the node UUIDs
     */
    public function __construct(OutputInterface $output, bool $identifiers = false)
    {
        parent::__construct($output);
        $this->identifiers = $identifiers;
    }

    public function setShowFullPath(bool $showFullPath): void
    {
        $this->showFullPath = $showFullPath;
    }

    /**
     * Print information about the visited node.
     *
     * @throws \Exception
     */
    public function visit(ItemInterface $item): void
    {
        if (!$item instanceof NodeInterface) {
            throw new \Exception('Internal error: did not expect to visit a non-node object: '.$item::class);
        }

        if (0 === $item->getDepth()) {
            $name = 'ROOT';
        } elseif ($this->showFullPath) {
            $name = $item->getPath();
        } else {
            $name = $item->getName();
        }

        $out = str_repeat('  ', $this->level)
            .'<comment>'.$name.'</comment>';
        if ($this->identifiers) {
            $identifier = $item->getIdentifier();
            if ($identifier) {
                $out .= "($identifier)";
            }
        }
        $out .= ':';
        $this->output->writeln($out);
    }
}

<?php

namespace PHPCR\Util\Console\Helper\TreeDumper;

use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\ItemVisitorInterface;
use PHPCR\ItemInterface;
use PHPCR\NodeInterface;

/**
 * TODO: this should base on the TraversingItemVisitor
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ConsoleDumperNodeVisitor implements ItemVisitorInterface
{
    protected $output;
    protected $identifiers;

    protected $level = 0;

    /**
     * @param OutputInterface $output
     * @param bool $identifiers whether to output the node UUID
     */
    public function __construct(OutputInterface $output, $identifiers = false)
    {
        $this->output = $output;
        $this->identifiers = $identifiers;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function visit(ItemInterface $item)
    {
        if (! $item instanceof NodeInterface) {
            throw new \Exception("Internal error: did not expect to visit a non-node object: $item");
        }

        $name = $item->getName();

        if ($item->getDepth() == 0) {
            $name = 'ROOT';
        }

        $out = str_repeat('  ', $this->level)
            . '<comment>' . $name . '</comment>';
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

<?php

declare(strict_types=1);

namespace PHPCR\Util;

use PHPCR\ItemVisitorInterface;
use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;

/**
 * TODO: this should base on the TraversingItemVisitor.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TreeWalker
{
    /**
     * Visitor for nodes.
     */
    protected ItemVisitorInterface $nodeVisitor;

    /**
     * Visitor for properties.
     */
    protected ?ItemVisitorInterface $propertyVisitor;

    /**
     * Filters to apply to decide whether a node needs to be visited.
     *
     * @var TreeWalkerFilterInterface[]
     */
    protected array $nodeFilters = [];

    /**
     * Filters to apply to decide whether a property needs to be visited.
     *
     * @var TreeWalkerFilterInterface[]
     */
    protected array $propertyFilters = [];

    /**
     * Instantiate a tree walker.
     *
     * @param ItemVisitorInterface      $nodeVisitor     The visitor for the nodes
     * @param ItemVisitorInterface|null $propertyVisitor The visitor for the nodes properties
     */
    public function __construct(ItemVisitorInterface $nodeVisitor, ItemVisitorInterface $propertyVisitor = null)
    {
        $this->nodeVisitor = $nodeVisitor;
        $this->propertyVisitor = $propertyVisitor;
    }

    /**
     * Add a filter to select the nodes that will be traversed.
     */
    public function addNodeFilter(TreeWalkerFilterInterface $filter): void
    {
        if (!in_array($filter, $this->nodeFilters, true)) {
            $this->nodeFilters[] = $filter;
        }
    }

    /**
     * Add a filter to select the properties that will be traversed.
     */
    public function addPropertyFilter(TreeWalkerFilterInterface $filter): void
    {
        if (!in_array($filter, $this->propertyFilters)) {
            $this->propertyFilters[] = $filter;
        }
    }

    /**
     * Return whether a node must be traversed or not.
     */
    protected function mustVisitNode(NodeInterface $node): bool
    {
        foreach ($this->nodeFilters as $filter) {
            if (!$filter->mustVisit($node)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return whether a node property must be traversed or not.
     */
    protected function mustVisitProperty(PropertyInterface $property): bool
    {
        foreach ($this->propertyFilters as $filter) {
            if (!$filter->mustVisit($property)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Traverse a node.
     *
     * @param int $recurse Max recursion level
     * @param int $level   Recursion level
     */
    public function traverse(NodeInterface $node, int $recurse = -1, int $level = 0): void
    {
        if ($this->mustVisitNode($node)) {
            // Visit node
            if (method_exists($this->nodeVisitor, 'setLevel')) {
                $this->nodeVisitor->setLevel($level);
            }
            if (method_exists($this->nodeVisitor, 'setShowFullPath')) {
                $this->nodeVisitor->setShowFullPath(0 === $level);
            }
            $node->accept($this->nodeVisitor);

            // Visit properties
            if (null !== $this->propertyVisitor) {
                foreach ($node->getProperties() as $prop) {
                    if ($this->mustVisitProperty($prop)) {
                        if (method_exists($this->propertyVisitor, 'setLevel')) {
                            $this->propertyVisitor->setLevel($level);
                        }
                        $prop->accept($this->propertyVisitor);
                    }
                }
            }

            // Visit children
            foreach ($node->getNodes() as $child) {
                if ($recurse < 0 || $level < $recurse) {
                    $this->traverse($child, $recurse, $level + 1);
                }
            }
        }
    }
}

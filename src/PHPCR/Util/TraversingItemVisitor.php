<?php

declare(strict_types=1);

namespace PHPCR\Util;

use PHPCR\ItemInterface;
use PHPCR\ItemVisitorInterface;
use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;
use PHPCR\RepositoryException;

/**
 * An implementation of ItemVisitor.
 *
 * TraversingItemVisitor is an abstract utility class which allows to easily
 * traverse an Item hierarchy.
 * You overwrite entering and leaving methods that get called for all
 * properties encountered
 *
 * TraversingItemVisitor makes use of the Visitor Pattern as described in the
 * book 'Design Patterns' by the Gang Of Four (Gamma et al.).
 * Tree traversal is done observing the left-to-right order of child Items if
 * such an order is supported and exists.
 *
 * @author Karsten Dambekalns <karsten@typo3.org>
 * @author Day Management AG, Switzerland
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @api
 */
abstract class TraversingItemVisitor implements ItemVisitorInterface
{
    /**
     * Indicates if traversal should be done in a breadth-first manner rather
     * than depth-first (which is the default).
     */
    protected bool $breadthFirst = false;

    /**
     * The 0-based depth up to which the hierarchy should be traversed (if it's
     * -1, the hierarchy will be traversed until there are no more children of
     * the current item).
     */
    protected int $maxDepth = -1;

    /**
     * Queue used to implement breadth-first traversal.
     */
    protected \SplQueue $currentQueue;

    /**
     * Queue used to implement breadth-first traversal.
     */
    protected \SplQueue $nextQueue;

    /**
     * Used to track hierarchy depth of item currently being processed.
     */
    protected int $currentDepth;

    /**
     * @param bool $breadthFirst if $breadthFirst is true then traversal is
     *                           done in a breadth-first manner; otherwise it is done in a
     *                           depth-first manner (which is the default behavior)
     * @param int  $maxDepth     the 0-based depth relative to the root node up
     *                           to which the hierarchy should be traversed (if it's -1, the
     *                           hierarchy will be traversed until there are no more children of the
     *                           current item)
     */
    public function __construct(bool $breadthFirst = false, int $maxDepth = -1)
    {
        $this->breadthFirst = $breadthFirst;
        $this->maxDepth = $maxDepth;

        if (true === $this->breadthFirst) {
            $this->currentQueue = new \SplQueue();
            $this->nextQueue = new \SplQueue();
        }
        $this->currentDepth = 0;
    }

    /**
     * Update the current depth level for indention.
     */
    public function setLevel(int $level): void
    {
        $this->currentDepth = $level;
    }

    /**
     * Implement this method to add behavior performed before a Property is
     * visited.
     *
     * @param ItemInterface $item  the Item that is accepting this
     *                             visitor
     * @param int           $depth hierarchy level of this node (the root node starts
     *                             at depth 0)
     *
     * @throws RepositoryException if an error occurs
     *
     * @api
     */
    abstract protected function entering(ItemInterface $item, int $depth): void;

    /**
     * Implement this method to add behavior performed after a Property is
     * visited.
     *
     * @param ItemInterface $item  the Item that is accepting this
     *                             visitor
     * @param int           $depth hierarchy level of this property (the root node
     *                             starts at depth 0)
     *
     * @throws RepositoryException if an error occurs
     *
     * @api
     */
    abstract protected function leaving(ItemInterface $item, int $depth): void;

    /**
     * Called when the Visitor is passed to an Item.
     *
     * It calls TraversingItemVisitor::entering() followed by
     * TraversingItemVisitor::leaving(). Implement these abstract methods to
     * specify behavior on 'arrival at' and 'after leaving' the $item.
     *
     * If this method throws, the visiting process is aborted.
     *
     * @param ItemInterface $item the Node or Property that is accepting
     *                            this visitor
     *
     * @throws RepositoryException if an error occurs
     *
     * @api
     */
    public function visit(ItemInterface $item): void
    {
        if (0 === $this->currentDepth) {
            $this->currentDepth = $item->getDepth();
        }
        if ($item instanceof PropertyInterface) {
            $this->entering($item, $this->currentDepth);
            $this->leaving($item, $this->currentDepth);
        } else {
            if (!$item instanceof NodeInterface) {
                throw new RepositoryException(sprintf(
                    'Internal error in TraversingItemVisitor: item %s at %s is not a node but %s',
                    $item->getName(),
                    $item->getPath(),
                    $item::class
                ));
            }

            try {
                if (false === $this->breadthFirst) {
                    $this->entering($item, $this->currentDepth);
                    if (-1 === $this->maxDepth || $this->currentDepth < $this->maxDepth) {
                        ++$this->currentDepth;
                        foreach ($item->getProperties() as $property) {
                            /* @var $property PropertyInterface */
                            $property->accept($this);
                        }
                        foreach ($item->getNodes() as $node) {
                            /* @var $node NodeInterface */
                            $node->accept($this);
                        }
                        --$this->currentDepth;
                    }
                    $this->leaving($item, $this->currentDepth);
                } else {
                    $this->entering($item, $this->currentDepth);
                    $this->leaving($item, $this->currentDepth);

                    if (-1 === $this->maxDepth || $this->currentDepth < $this->maxDepth) {
                        foreach ($item->getProperties() as $property) {
                            /* @var $property PropertyInterface */
                            $property->accept($this);
                        }
                        foreach ($item->getNodes() as $node) {
                            /* @var $node NodeInterface */
                            $node->accept($this);
                        }
                    }

                    while (!$this->currentQueue->isEmpty() || !$this->nextQueue->isEmpty()) {
                        if ($this->currentQueue->isEmpty()) {
                            ++$this->currentDepth;
                            $this->currentQueue = $this->nextQueue;
                            $this->nextQueue = new \SplQueue();
                        }
                        $item = $this->currentQueue->dequeue();
                        $item->accept($this);
                    }
                    $this->currentDepth = 0;
                }
            } catch (RepositoryException $exception) {
                $this->currentDepth = 0;

                throw $exception;
            }
        }
    }
}

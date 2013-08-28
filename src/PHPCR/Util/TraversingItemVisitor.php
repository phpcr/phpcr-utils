<?php

namespace PHPCR\Util;

use SplQueue;

use PHPCR\ItemInterface;
use PHPCR\PropertyInterface;
use PHPCR\NodeInterface;
use PHPCR\ItemVisitorInterface;
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
 *
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
     *
     * @var boolean
     */
    protected $breadthFirst = false;

    /**
     * The 0-based depth up to which the hierarchy should be traversed (if it's
     * -1, the hierarchy will be traversed until there are no more children of
     * the current item).
     *
     * @var integer
     */
    protected $maxDepth = -1;

    /**
     * Queue used to implement breadth-first traversal.
     *
     * @var SplQueue
     */
    protected $currentQueue;

    /**
     * Queue used to implement breadth-first traversal.
     *
     * @var SplQueue
     */
    protected $nextQueue;

    /**
     * Used to track hierarchy depth of item currently being processed.
     *
     * @var integer
     */
    protected $currentDepth;

    /**
     * Constructs a new instance of this class.
     *
     * @param boolean $breadthFirst if $breadthFirst is true then traversal is
     *      done in a breadth-first manner; otherwise it is done in a
     *      depth-first manner (which is the default behavior).
     * @param integer $maxDepth the 0-based depth relative to the root node up
     *      to which the hierarchy should be traversed (if it's -1, the
     *      hierarchy will be traversed until there are no more children of the
     *      current item).
     *
     * @api
     */
    public function __construct($breadthFirst = false, $maxDepth = -1)
    {
        $this->breadthFirst = $breadthFirst;
        $this->maxDepth = $maxDepth;

        if ($this->breadthFirst === true) {
            $this->currentQueue = new SplQueue();
            $this->nextQueue = new SplQueue();
        }
        $this->currentDepth = 0;
    }

    /**
     * Update the current depth level for indention
     *
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->currentDepth = $level;
    }

    /**
     * Implement this method to add behavior performed before a Property is
     * visited.
     *
     * @param ItemInterface $item the Item that is accepting this
     *      visitor.
     * @param integer $depth hierarchy level of this node (the root node starts
     *      at depth 0).
     *
     * @throws RepositoryException if an error occurs.
     *
     * @api
     */
    abstract protected function entering(ItemInterface $item, $depth);

    /**
     * Implement this method to add behavior performed after a Property is
     * visited.
     *
     * @param ItemInterface $item the Item that is accepting this
     *      visitor.
     * @param integer $depth hierarchy level of this property (the root node
     *      starts at depth 0).
     *
     * @throws RepositoryException if an error occurs.
     *
     * @api
     */
    abstract protected function leaving(ItemInterface $item, $depth);

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
     *      this visitor.
     *
     * @throws RepositoryException if an error occurs.
     *
     * @api
     */
    public function visit(ItemInterface $item)
    {
        if ($this->currentDepth == 0) {
            $this->currentDepth = $item->getDepth();
        }
        if ($item instanceof PropertyInterface) {
            $this->entering($item, $this->currentDepth);
            $this->leaving($item, $this->currentDepth);
        } else {
            /** @var $item NodeInterface */
            try {
                if ($this->breadthFirst === false) {
                    $this->entering($item, $this->currentDepth);
                    if ($this->maxDepth == -1 || $this->currentDepth < $this->maxDepth) {
                        $this->currentDepth++;
                        foreach ($item->getProperties() as $property) {
                            /** @var $property PropertyInterface */
                            $property->accept($this);
                        }
                        foreach ($item->getNodes() as $node) {
                            /** @var $node NodeInterface */
                            $node->accept($this);
                        }
                        $this->currentDepth--;
                    }
                    $this->leaving($item, $this->currentDepth);
                } else {
                    $this->entering($item, $this->currentDepth);
                    $this->leaving($item, $this->currentDepth);

                    if ($this->maxDepth == -1 || $this->currentDepth < $this->maxDepth) {
                        foreach ($item->getProperties() as $property) {
                            /** @var $property PropertyInterface */
                            $property->accept($this);
                        }
                        foreach ($item->getNodes() as $node) {
                            /** @var $node NodeInterface */
                            $node->accept($this);
                        }
                    }

                    while (!$this->currentQueue->isEmpty() || !$this->nextQueue->isEmpty()) {
                        if ($this->currentQueue->isEmpty()) {
                            $this->currentDepth++;
                            $this->currentQueue = $this->nextQueue;
                            $this->nextQueue = new SplQueue();
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

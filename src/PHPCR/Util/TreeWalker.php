<?php

/**
 * This file is part of the PHPCR Utils
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */

namespace PHPCR\Util;

use PHPCR\ItemVisitorInterface;
use \PHPCR\NodeInterface;
use \PHPCR\PropertyInterface;

/**
 * TODO: this should base on the TraversingItemVisitor
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class TreeWalker
{
    /**
     * Visitor for nodes
     *
     * @var ItemVisitorInterface
     */
    protected $nodeVisitor;

    /**
     * Visitor for properties
     *
     * @var ItemVisitorInterface
     */
    protected $propertyVisitor;

    /**
     * Filters to apply to decide whether a node needs to be visited
     *
     * @var array()
     */
    protected $nodeFilters = array();

    /**
     * Filters to apply to decide whether a property needs to be visited
     *
     * @var array()
     */
    protected $propertyFilters = array();

    /**
     * Instantiate a tree walker
     *
     * @param ItemVisitorInterface $nodeVisitor     The visitor for the nodes
     * @param ItemVisitorInterface $propertyVisitor The visitor for the nodes properties
     */
    public function __construct(ItemVisitorInterface $nodeVisitor, ItemVisitorInterface $propertyVisitor = null)
    {
        $this->nodeVisitor = $nodeVisitor;
        $this->propertyVisitor = $propertyVisitor;
    }

    /**
     * Add a filter to select the nodes that will be traversed
     *
     * @param TreeWalkerFilterInterface $filter
     */
    public function addNodeFilter(TreeWalkerFilterInterface $filter)
    {
        if (!array_search($filter, $this->nodeFilters)) {
            $this->nodeFilters[] = $filter;
        }
    }

    /**
     * Add a filter to select the properties that will be traversed
     *
     * @param TreeWalkerFilterInterface $filter
     */
    public function addPropertyFilter(TreeWalkerFilterInterface $filter)
    {
        if (!array_search($filter, $this->propertyFilters)) {
            $this->propertyFilters[] = $filter;
        }
    }

    /**
     * Return whether a node must be traversed or not
     *
     * @param NodeInterface $node
     *
     * @return boolean
     */
    protected function mustVisitNode(NodeInterface $node)
    {
        foreach ($this->nodeFilters as $filter) {
            if (! $filter->mustVisit($node)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return whether a node property must be traversed or not
     *
     * @param PropertyInterface $property
     *
     * @return boolean
     */
    protected function mustVisitProperty(PropertyInterface $property)
    {
        foreach ($this->propertyFilters as $filter) {
            if (! $filter->mustVisit($property)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Traverse a node
     *
     * @param NodeInterface $node
     * @param int           $recurse Max recursion level
     * @param int           $level   Recursion level
     */
    public function traverse(NodeInterface $node, $recurse = -1, $level = 0)
    {
        if ($this->mustVisitNode($node)) {

            // Visit node
            $this->nodeVisitor->setLevel($level);
            $node->accept($this->nodeVisitor);

            // Visit properties
            if ($this->propertyVisitor !== null) {
                foreach ($node->getProperties() as $prop) {
                    if ($this->mustVisitProperty($prop)) {
                        $this->propertyVisitor->setLevel($level);
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

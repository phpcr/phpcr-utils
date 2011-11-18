<?php

namespace PHPCR\Util;

use PHPCR\SessionInterface;
use PHPCR\ItemInterface;

/**
 * Helper with only static methods to work with PHPCR nodes
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author David Buchmann <david@liip.ch>
 */
class NodeHelper
{
    /**
     * Do not create an instance of this class
     */
    private function __construct()
    {
    }

    /**
     * Create a node and it's parents, if necessary.  Like mkdir -p.
     *
     * @param SessionInterface $session the phpcr session to create the path
     * @param string $path  full path, like /content/jobs/data
     *
     * @return PHPCR\NodeInterface the last node of the path, i.e. data
     */
    public static function createPath(SessionInterface $session, $path)
    {
        $current = $session->getRootNode();

        $segments = preg_split('#/#', $path, null, PREG_SPLIT_NO_EMPTY);
        foreach ($segments as $segment) {
            if ($current->hasNode($segment)) {
                $current = $current->getNode($segment);
            } else {
                $current = $current->addNode($segment);
            }
        }

        return $current;
    }

    /**
     * Delete all the nodes in the repository which are not in a system namespace
     *
     * Note that if you want to delete a node under your root node, you can just
     * use the remove method on that node. This method is just here to help you
     * because the implemenation might add nodes like jcr:system to the root
     * node which you are not allowed to remove.
     *
     * @param SessionInterface $session the session to remove all children of
     *      the root node
     *
     * @see isSystemItem
     */
    public static function deleteAllNodes(SessionInterface $session)
    {
        $root = $session->getRootNode();
        foreach ($root->getNodes() as $node) {
            if (! self::isSystemItem($node)) {
                $node->remove();
            }
        }
        foreach ($root->getProperties() as $property) {
            if (! self::isSystemItem($property)) {
                $property->remove();
            }
        }
    }

    /**
     * Determine whether this item has a namespace that is to be considered
     * a system namespace
     */
    public static function isSystemItem(ItemInterface $item)
    {
        $name = $item->getName();
        return strpos($name, 'jcr:') === 0 || strpos($name, 'rep:') === 0;
    }
}

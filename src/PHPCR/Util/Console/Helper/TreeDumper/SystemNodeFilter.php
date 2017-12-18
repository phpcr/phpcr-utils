<?php

namespace PHPCR\Util\Console\Helper\TreeDumper;

use PHPCR\ItemInterface;
use PHPCR\RepositoryException;
use PHPCR\Util\NodeHelper;
use PHPCR\Util\TreeWalkerFilterInterface;

/**
 * Filter system properties and nodes based on their name.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class SystemNodeFilter implements TreeWalkerFilterInterface
{
    /**
     * Checks whether this item is a system item.
     *
     * @param ItemInterface $item
     *
     * @throws RepositoryException
     *
     * @return bool
     */
    public function mustVisit(ItemInterface $item)
    {
        return !NodeHelper::isSystemItem($item);
    }
}

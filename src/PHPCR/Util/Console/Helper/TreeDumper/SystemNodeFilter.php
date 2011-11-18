<?php

namespace PHPCR\Util\Console\Helper\TreeDumper;

use PHPCR\ItemInterface;
use PHPCR\Util\TreeWalkerFilterInterface;
use PHPCR\Util\NodeHelper;

/**
 * Filter system properties and nodes based on their name.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class SystemNodeFilter implements TreeWalkerFilterInterface
{
    public function mustVisit(ItemInterface $node)
    {
        return ! NodeHelper::isSystemItem($node);
    }
}

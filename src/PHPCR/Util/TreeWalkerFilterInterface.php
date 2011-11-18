<?php

namespace PHPCR\Util;

use PHPCR\ItemInterface;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface TreeWalkerFilterInterface
{
    public function mustVisit(ItemInterface $node);
}

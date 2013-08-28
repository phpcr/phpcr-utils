<?php

namespace PHPCR\Util;

use PHPCR\ItemInterface;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface TreeWalkerFilterInterface
{
    /**
     * Whether to visit the passed item
     *
     * @param \PHPCR\ItemInterface $item
     *
     * @return mixed
     */
    public function mustVisit(ItemInterface $item);
}

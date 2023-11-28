<?php

declare(strict_types=1);

namespace PHPCR\Util;

use PHPCR\ItemInterface;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface TreeWalkerFilterInterface
{
    /**
     * Whether to visit the passed item.
     */
    public function mustVisit(ItemInterface $item): mixed;
}

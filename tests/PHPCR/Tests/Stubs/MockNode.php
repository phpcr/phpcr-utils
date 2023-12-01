<?php

declare(strict_types=1);

namespace PHPCR\Tests\Stubs;

use PHPCR\NodeInterface;

/**
 * @implements  \Iterator<string, NodeInterface>
 */
abstract class MockNode implements \Iterator, NodeInterface
{
}

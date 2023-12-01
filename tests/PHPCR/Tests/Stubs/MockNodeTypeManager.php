<?php

declare(strict_types=1);

namespace PHPCR\Tests\Stubs;

use PHPCR\NodeType\NodeTypeInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;

/**
 * @implements \Iterator<string, NodeTypeInterface>
 */
abstract class MockNodeTypeManager implements \Iterator, NodeTypeManagerInterface
{
}

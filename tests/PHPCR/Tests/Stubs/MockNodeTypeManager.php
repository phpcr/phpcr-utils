<?php

declare(strict_types=1);

namespace PHPCR\Tests\Stubs;

use PHPCR\NodeType\NodeTypeManagerInterface;

abstract class MockNodeTypeManager implements \Iterator, NodeTypeManagerInterface
{
}

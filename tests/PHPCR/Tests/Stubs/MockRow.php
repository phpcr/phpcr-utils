<?php

declare(strict_types=1);

namespace PHPCR\Tests\Stubs;

use PHPCR\Query\RowInterface;

abstract class MockRow implements \Iterator, RowInterface
{
}

<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util;

use PHPCR\Util\UUIDHelper;
use PHPUnit\Framework\TestCase;

class UUIDHelperTest extends TestCase
{
    public function testGenerateUUID(): void
    {
        $id = UUIDHelper::generateUUID();
        $this->assertEquals(1, preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/', $id));
    }

    public function testIsUUID(): void
    {
        $this->assertTrue(UUIDHelper::isUUID('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertTrue(UUIDHelper::isUUID('00000000-0000-0000-C000-000000000046'));
        $this->assertFalse(UUIDHelper::isUUID('not a uuid'));
        $this->assertFalse(UUIDHelper::isUUID('123456'));
    }
}

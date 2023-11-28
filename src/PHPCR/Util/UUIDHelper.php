<?php

declare(strict_types=1);

namespace PHPCR\Util;

use Ramsey\Uuid\Uuid;

/**
 * Static helper functions to deal with Universally Unique IDs (UUID).
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class UUIDHelper
{
    /**
     * Checks if the string could be a UUID.
     *
     * @param string $id Possible uuid
     *
     * @return bool true if the test was passed, else false
     */
    public static function isUUID(string $id): bool
    {
        // UUID is HEX_CHAR{8}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{12}
        return 1 === preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/', $id);
    }

    /**
     * Generate a UUID.
     *
     * This UUID can not be guaranteed to be unique within the repository.
     * Ensuring this is the responsibility of the repository implementation.
     *
     * It also allows the use of Ramsey\Uuid\Uuid class.
     *
     * @return string a random UUID
     */
    public static function generateUUID(): string
    {
        if (class_exists(Uuid::class)) {
            return Uuid::uuid4()->toString();
        }

        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF),

            // 16 bits for "time_mid"
            random_int(0, 0xFFFF),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            random_int(0, 0x0FFF) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            random_int(0, 0x3FFF) | 0x8000,

            // 48 bits for "node"
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF),
            random_int(0, 0xFFFF)
        );
    }
}

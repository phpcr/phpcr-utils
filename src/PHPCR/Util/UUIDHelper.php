<?php

namespace PHPCR\Util;

use Rhumsaa\Uuid\Uuid;

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
     * @return bool True if the test was passed, else false.
     */
    public static function isUUID($id)
    {
        // UUID is HEX_CHAR{8}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{4}-HEX_CHAR{12}
        if (1 === preg_match('/^[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}$/', $id)) {
            return true;
        }

        return false;
    }

    /**
     * Generate a UUID.
     *
     * @return string a random UUID
     */
    public static function generateUUID()
    {
        $uuid4 = Uuid::uuid4();

        return $uuid4->toString();
    }
}

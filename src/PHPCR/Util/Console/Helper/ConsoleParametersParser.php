<?php

/**
 * This file is part of the PHPCR Utils
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */

namespace PHPCR\Util\Console\Helper;

/**
 * Helper class for console interaction.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ConsoleParametersParser
{
    /**
     * Return true if $value is a string that can be considered as true.
     * I.e. if it is case insensitively "true" or "yes".
     *
     * @param string $value
     *
     * @return boolean
     */
    public static function isTrueString($value)
    {
        $value = strtolower($value);

        return $value === 'true' || $value === 'yes';
    }

    /**
     * Return true if $value is a string that can be considered as false.
     * I.e. if it is case insensitively "false" or "no".
     *
     * @param string $value
     *
     * @return boolean
     */
    public static function isFalseString($value)
    {
        $value = strtolower($value);

        return $value === 'false' || $value === 'no';
    }
}

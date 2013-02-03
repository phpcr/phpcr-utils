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

namespace PHPCR\Util;

use PHPCR\SessionInterface;
use PHPCR\ItemInterface;
use PHPCR\RepositoryException;
use PHPCR\NamespaceException;

/**
 * Static methods to handle path operations for PHPCR implementations
 *
 * @author David Buchmann <david@liip.ch>
 */
class PathHelper
{
    /**
     * Do not create an instance of this class
     */
    private function __construct()
    {
    }

    /**
     * Check whether this is a syntactically valid absolute path.
     *
     * The JCR specification is posing few limits on valid paths, your
     * implementation probably wants to provide its own code based on this one
     *
     * Non-normalized paths are considered invalid, i.e. /node/. is /node and /my/node/.. is /my
     *
     * @param string $path The path to validate
     * @param bool $destination whether this is a destination path (by copy or
     *      move), meaning [] is not allowed. If your implementation does not
     *      support same name siblings, just always pass true for this
     * @param bool $throw whether to throw an exception on validation errors
     *
     * @return bool true if valid, false if not valid and $throw was false
     *
     * @throws RepositoryException if the path contains invalid characters and $throw is true
     */
    public static function assertValidAbsolutePath($path, $destination = false, $throw = true)
    {
        if (! is_string($path)
            || strlen($path) == 0
            || '/' !== $path[0]
            || strlen($path) > 1 && '/' === $path[strlen($path) - 1]
            || preg_match('-//|/\./|/\.\./-', $path)
        ) {
            if ($throw) {
                throw new RepositoryException("Invalid path $path");
            }

            return false;
        }
        if ($destination && ']' === $path[strlen($path) - 1]) {
            if ($throw) {
                throw new RepositoryException("Destination path may not end with index $path");
            }

            return false;

        }

        return true;
    }

    /**
     * Minimal check to see if this local node name conforms to the jcr
     * specification for a name without the namespace.
     *
     * Note that the empty string is actually a valid node name (the root node)
     *
     * If it can't be avoided, implementations may restrict validity further,
     * but this will reduce interchangeability, thus it is better to properly
     * encode and decode characters that are not natively allowed by a storage
     * engine.
     *
     * @param string $name The name to check
     *
     * @return bool true if valid, false if not valid and $throw was false
     *
     * @throws RepositoryException if the name is invalid and $throw is true
     *
     * @see http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.2.2%20Local%20Names
     */
    public static function assertValidLocalName($name, $throw = true)
    {
        if ('.' == $name || '..' == $name) {
            throw new RepositoryException('Name may not be parent or self identifier: ' . $name);
        }

        if (preg_match('/\\/|:|\\[|\\]|\\||\\*/', $name)) {
            throw new RepositoryException('Name contains illegal characters: '.$name);
        }

        return true;
    }

    /**
     * Normalize path according to JCR's spec (3.4.5) and then validates it
     * using the assertValidAbsolutePath method.
     *
     * <ul>
     *   <li>All self segments(.) are removed.</li>
     *   <li>All redundant parent segments(..) are collapsed.</li>
     * </ul>
     *
     * Note: A well-formed input path implies a well-formed and normalized path returned.
     *
     * @param string $path The path to normalize.
     * @param bool $destination whether this is a destination path (by copy or
     *      move), meaning [] is not allowed in validation.
     * @param bool $throw whether to throw an exception if validation fails or
     *      just to return false.
     *
     * @return string The normalized path or false if $throw was false and the path invalid
     */
    public static function normalizePath($path, $destination = false, $throw = true)
    {
        if (! is_string($path) || strlen($path) === 0) {
            throw new RepositoryException('Expected string but got ' . gettype($path));
        }

        if ('/' === $path) {
            $normalizedPath = '/';
        } else {
            $finalParts= array();
            $parts = explode('/', $path);

            foreach ($parts as $pathPart) {
                switch ($pathPart) {
                    case '.':
                        break;
                    case '..':
                        if (count($finalParts) > 1) {
                            // do not remove leading slash. "/.." is "/", not ""
                            array_pop($finalParts);
                        }
                        break;
                    default:
                        $finalParts[] = $pathPart;
                        break;
                }
            }
            $normalizedPath =  count($finalParts) > 1 ?
                implode('/', $finalParts) :
                '/'; // first element is always the empty-name root element
        }
        if (! self::assertValidAbsolutePath($normalizedPath, $destination, $throw)) {

            return false;
        }

        return $normalizedPath;
    }

    /**
     * Get the parent path of a valid absolute path.
     *
     * @param string $path the path to get the parent from
     *
     * @return string the path with the last segment removed
     */
    public static function getParentPath($path)
    {
        if ('/' === $path) {

            return $path;
        }

        return substr($path, 0, strrpos($path, '/'));
    }

    /**
     * Get the name from the path, including eventual namespace prefix.
     *
     * You must make sure to only pass valid paths to this method.
     *
     * @param string $path a valid absolute path, like /content/jobs/data
     *
     * @return string the name, that is the string after the last "/"
     */
    public static function getNodeName($path)
    {
        return substr($path, strrpos($path, '/') + 1);
    }
}

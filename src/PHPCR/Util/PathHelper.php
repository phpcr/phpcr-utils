<?php

namespace PHPCR\Util;

use PHPCR\RepositoryException;

/**
 * Static methods to handle path operations for PHPCR implementations
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author David Buchmann <david@liip.ch>
 */
class PathHelper
{
    /**
     * Do not create an instance of this class
     */
    // @codeCoverageIgnoreStart
    private function __construct()
    {
    }
    // @codeCoverageIgnoreEnd

    /**
     * Check whether this is a syntactically valid absolute path.
     *
     * The JCR specification is posing few limits on valid paths, your
     * implementation probably wants to provide its own code based on this one
     *
     * Non-normalized paths are considered invalid, i.e. /node/. is /node and /my/node/.. is /my
     *
     * @param string $path        The path to validate
     * @param bool   $destination whether this is a destination path (by copy or
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
        if ((!is_string($path) && !is_numeric($path))
            || strlen($path) == 0
            || '/' !== $path[0]
            || strlen($path) > 1 && '/' === $path[strlen($path) - 1]
            || preg_match('-//|/\./|/\.\./-', $path)
        ) {
            return self::error("Invalid path $path", $throw);
        }
        if ($destination && ']' === $path[strlen($path) - 1]) {
            return self::error("Destination path may not end with index $path", $throw);
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
     * @param string  $name  The name to check
     * @param boolean $throw whether to throw an exception on validation errors.
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
            return self::error("Name may not be parent or self identifier: $name", $throw);
        }

        if (preg_match('/\\/|:|\\[|\\]|\\||\\*/', $name)) {
            return self::error("Name contains illegal characters: $name", $throw);
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
     * @param string $path        The path to normalize.
     * @param bool   $destination whether this is a destination path (by copy or
     *      move), meaning [] is not allowed in validation.
     * @param bool $throw whether to throw an exception if validation fails or
     *      just to return false.
     *
     * @return string The normalized path or false if $throw was false and the path invalid
     *
     * @throws RepositoryException if the path is not a valid absolute path and
     *      $throw is true
     */
    public static function normalizePath($path, $destination = false, $throw = true)
    {
        if (!is_string($path) && !is_numeric($path)) {
            return self::error('Expected string but got ' . gettype($path), $throw);
        }
        if (strlen($path) === 0) {
            return self::error('Path must not be of zero length', $throw);
        }

        if ('/' === $path) {
            return '/';
        }

        if ('/' !== $path[0]) {
            return self::error("Not an absolute path '$path'", $throw);
        }

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
            '/'  // first element is always the empty-name root element. this might have been a path like /x/..
        ;

        if (! self::assertValidAbsolutePath($normalizedPath, $destination, $throw)) {
            return false;
        }

        return $normalizedPath;
    }

    /**
     * In addition to normalizing and validating, this method combines the path
     * with a context if it is not absolute.
     *
     * @param string $path        A relative or absolute path
     * @param string $context     The absolute path context to make $path absolute if needed
     * @param bool   $destination whether this is a destination path (by copy or
     *      move), meaning [] is not allowed in validation.
     * @param bool $throw whether to throw an exception if validation fails or
     *      just to return false.
     *
     * @return string The normalized, absolute path or false if $throw was
     *      false and the path invalid
     *
     * @throws RepositoryException if the path can not be made into a valid
     *      absolute path and $throw is true
     */
    public static function absolutizePath($path, $context, $destination = false, $throw = true)
    {
        if (!is_string($path) && !is_numeric($path)) {
            return self::error('Expected string path but got ' . gettype($path), $throw);
        }
        if (!is_string($context)) {
            return self::error('Expected string context but got ' . gettype($context), $throw);
        }
        if (strlen($path) === 0) {
            return self::error('Path must not be of zero length', $throw);
        }

        if ('/' !== $path[0]) {
            $path = ('/' === $context) ? "/$path" : "$context/$path";
        }

        return self::normalizePath($path, $destination, $throw);
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
            return '/';
        }

        $pos = strrpos($path, '/');

        if (0 === $pos) {
            return '/';
        }

        return substr($path, 0, $pos);
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

    /**
     * Get the depth of the path, ignore trailing slashes, root starts counting at 0
     *
     * @param string $path a valid absolute path, like /content/jobs/data
     *
     * @return integer with the path depth
     */
    public static function getPathDepth($path)
    {
        return substr_count(rtrim($path, '/'), '/');
    }

    /**
     * If $throw is true, throw a RepositoryException with $msg. Otherwise
     * return false.
     *
     * @param string  $msg   the exception message to use in case of throw being true
     * @param boolean $throw whether to throw the exception or return false
     *
     * @return boolean false
     *
     * @throws RepositoryException
     */
    private static function error($msg, $throw)
    {
        if ($throw) {
            throw new RepositoryException($msg);
        }

        return false;
    }
}

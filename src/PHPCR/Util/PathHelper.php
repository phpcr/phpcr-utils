<?php

declare(strict_types=1);

namespace PHPCR\Util;

use PHPCR\NamespaceException;
use PHPCR\RepositoryException;

/**
 * Static methods to handle path operations for PHPCR implementations.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Buchmann <mail@davidbu.ch>
 */
class PathHelper
{
    /**
     * Do not create an instance of this class.
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
     * @param string     $path              The path to validate
     * @param bool       $destination       whether this is a destination path (by copy or
     *                                      move), meaning [] is not allowed. If your implementation does not
     *                                      support same name siblings, just always pass true for this
     * @param bool       $throw             whether to throw an exception on validation errors
     * @param bool|array $namespacePrefixes List of all known namespace prefixes.
     *                                      If specified, this method validates that the path contains no unknown prefixes.
     *
     * @return bool true if valid, false if not valid and $throw was false
     *
     * @throws RepositoryException if the path contains invalid characters and $throw is true
     */
    public static function assertValidAbsolutePath(string $path, bool $destination = false, bool $throw = true, bool|array $namespacePrefixes = false): bool
    {
        if ('' === $path
            || '/' !== $path[0]
            || preg_match('-//|/\./|/\.\./-', $path)
            || (strlen($path) > 1 && '/' === $path[strlen($path) - 1])
        ) {
            return self::error("Invalid path '$path'", $throw);
        }
        if ($destination && ']' === $path[strlen($path) - 1]) {
            return self::error("Destination path may not end with index: '$path'", $throw);
        }
        if ($namespacePrefixes) {
            $matches = [];
            preg_match_all('#/(?P<prefixes>[^/:]+):#', $path, $matches);
            $unknown = array_diff(array_unique($matches['prefixes']), $namespacePrefixes);
            if (count($unknown)) {
                if (!$throw) {
                    return false;
                }

                throw new NamespaceException(sprintf('Unknown namespace prefix(es) (%s) in path %s', implode(' and ', $unknown), $path));
            }
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
     * @param string $name  The name to check
     * @param bool   $throw whether to throw an exception on validation errors
     *
     * @return bool true if valid, false if not valid and $throw was false
     *
     * @throws RepositoryException if the name is invalid and $throw is true
     *
     * @see http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.2.2%20Local%20Names
     */
    public static function assertValidLocalName(string $name, bool $throw = true): bool
    {
        if ('.' === $name || '..' === $name) {
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
     * @param string $path        the path to normalize
     * @param bool   $destination whether this is a destination path (by copy or
     *                            move), meaning [] is not allowed in validation
     * @param bool   $throw       whether to throw an exception if validation fails or
     *                            just to return false
     *
     * @return false|string The normalized path or false if $throw was false and the path invalid
     *
     * @throws RepositoryException if the path is not a valid absolute path and
     *                             $throw is true
     */
    public static function normalizePath(string $path, bool $destination = false, bool $throw = true): false|string
    {
        if ('' === $path) {
            return self::error('Path must not be of zero length', $throw);
        }

        if ('/' === $path) {
            return '/';
        }

        if ('/' !== $path[0]) {
            return self::error("Not an absolute path '$path'", $throw);
        }

        $finalParts = [];
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
        $normalizedPath = count($finalParts) > 1 ?
            implode('/', $finalParts) :
            '/';  // first element is always the empty-name root element. this might have been a path like /x/..

        if (!self::assertValidAbsolutePath($normalizedPath, $destination, $throw)) {
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
     *                            move), meaning [] is not allowed in validation
     * @param bool   $throw       whether to throw an exception if validation fails or
     *                            just to return false
     *
     * @return false|string The normalized, absolute path or false if $throw was
     *                      false and the path invalid
     *
     * @throws RepositoryException if the path can not be made into a valid
     *                             absolute path and $throw is true
     */
    public static function absolutizePath(string $path, string $context, bool $destination = false, bool $throw = true): false|string
    {
        if ('' === $path) {
            return self::error('Path must not be of zero length', $throw);
        }

        if ('/' !== $path[0]) {
            $path = ('/' === $context) ? "/$path" : "$context/$path";
        }

        return self::normalizePath($path, $destination, $throw);
    }

    /**
     * Make an absolute path relative to $context.
     *
     * ie. $context . '/' . PathHelper::relativePath($path, $context) === $path
     *
     * Input paths are assumed to be normalized.
     *
     * @param string $path    The absolute path to a node
     * @param string $context The absolute path to an ancestor of $path
     * @param bool   $throw   whether to throw exceptions on invalid data
     *
     * @return false|string The relative path from $context to $path
     */
    public static function relativizePath(string $path, string $context, bool $throw = true): false|string
    {
        if (!str_starts_with($path, $context)) {
            return self::error("$path is not within $context", $throw);
        }

        return ltrim(substr($path, strlen($context)), '/');
    }

    /**
     * Get the parent path of a valid absolute path.
     *
     * @param string $path the path to get the parent from
     *
     * @return string the path with the last segment removed
     */
    public static function getParentPath(string $path): string
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
     *
     * @throws RepositoryException
     */
    public static function getNodeName(string $path): string
    {
        $strrpos = strrpos($path, '/');

        if (false === $strrpos) {
            self::error(sprintf(
                'Path "%s" must be an absolute path',
                $path
            ), true);
        }

        return substr($path, $strrpos + 1);
    }

    /**
     * Return the localname of the node at the given path.
     * The local name is the node name minus the namespace.
     *
     * @param string $path a valid absolute path
     *
     * @throws RepositoryException
     */
    public static function getLocalNodeName(string $path): string
    {
        $nodeName = self::getNodeName($path);
        $localName = strstr($nodeName, ':');

        if (false !== $localName) {
            return substr($localName, 1);
        }

        return $nodeName;
    }

    /**
     * Get the depth of the path, ignore trailing slashes, root starts counting at 0.
     *
     * @param string $path a valid absolute path, like /content/jobs/data
     *
     * @return int with the path depth
     */
    public static function getPathDepth(string $path): int
    {
        return substr_count(rtrim($path, '/'), '/');
    }

    /**
     * If $throw is true, throw a RepositoryException with $msg. Otherwise
     * return false.
     *
     * @param string $msg   the exception message to use in case of throw being true
     * @param bool   $throw whether to throw the exception or return false
     *
     * @return false
     *
     * @throws RepositoryException
     */
    private static function error(string $msg, bool $throw): bool
    {
        if ($throw) {
            throw new RepositoryException($msg);
        }

        return false;
    }
}

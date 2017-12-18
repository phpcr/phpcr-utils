<?php

namespace PHPCR\Tests\Util;

use PHPCR\NamespaceException;
use PHPCR\RepositoryException;
use PHPCR\Util\PathHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

class PathHelperTest extends TestCase
{
    // assertValidPath tests

    /**
     * @dataProvider dataproviderValidAbsolutePaths
     */
    public function testAssertValidAbsolutePath($path, $destination = false)
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath($path, $destination));
    }

    public function dataproviderValidAbsolutePaths()
    {
        return [
            ['/parent/child'],
            ['/'],
            ['/jcr:foo_/b-a/0^.txt'],
            ['/parent[7]/child'],
            ['/parent[7]/child', true], // index is allowed in destination parent path, only not in last element
            ['/parent[7]/child[3]'],
        ];
    }

    /**
     * @dataProvider dataproviderInvalidAbsolutePaths
     */
    public function testAssertInvalidAbsolutePath($path, $destination = false)
    {
        $this->expectException(RepositoryException::class);

        PathHelper::assertValidAbsolutePath($path, $destination);
    }

    /**
     * @dataProvider dataproviderValidAbsolutePathsWithNamespaces
     */
    public function testAssertAbsolutePathNamespace($path)
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath($path, false, true, ['jcr', 'nt']));
    }

    public function dataproviderValidAbsolutePathsWithNamespaces()
    {
        return [
            ['/parent/child'],
            ['/jcr:localname'],
            ['/jcr:localname/test'],
            ['/jcr:localname/test/nt:node'],
            ['/jcr:localname/test/nt:node/bla'],
            ['/'],
            ['/jcr:foo_/b-a/0^.txt'],
            ['/parent[7]/child'],
            ['/jcr:localname[3]/test'],
        ];
    }

    /**
     * @expectedExceptionMessage invalidprefix and other-ns
     */
    public function testAssertInvalidNamespaceAbsolutePath()
    {
        $this->expectException(NamespaceException::class);

        PathHelper::assertValidAbsolutePath('/invalidprefix:localname/other-ns:test/invalidprefix:node/bla', false, true, ['jcr', 'nt']);
    }

    /**
     * @dataProvider dataproviderInvalidAbsolutePaths
     */
    public function testAssertInvalidAbsolutePathNoThrow($path, $destination = false)
    {
        $this->assertFalse(PathHelper::assertValidAbsolutePath($path, $destination, false));
    }

    public function dataproviderInvalidAbsolutePaths()
    {
        return [
            ['/parent/child[7]', true], // destination last element with index
            ['parent'], // not absolute
            ['/parent//child'],
            ['//'],
            ['/parent/../child'],
            ['/parent/./child'],
            ['/parent/child/'],
        ];
    }

    // assertValidLocalName tests

    public function testAssertValidLocalName()
    {
        $this->assertTrue(PathHelper::assertValidLocalName('nodename'));
    }

    public function testAssertValidLocalNameRootnode()
    {
        $this->assertTrue(PathHelper::assertValidLocalName(''));
    }

    /**
     * @dataProvider dataproviderInvalidLocalNames
     */
    public function testAssertInvalidLocalName($name)
    {
        $this->expectException(RepositoryException::class);

        PathHelper::assertValidLocalName($name);
    }

    public function dataproviderInvalidLocalNames()
    {
        return [
            ['jcr:nodename'],
            ['/path'],
            ['.'],
            ['..'],
        ];
    }

    // normalizePath tests

    /**
     * @dataProvider dataproviderNormalizePath
     */
    public function testNormalizePath($inputPath, $outputPath)
    {
        $this->assertSame($outputPath, PathHelper::normalizePath($inputPath));
    }

    public static function dataproviderNormalizePath()
    {
        return [
            ['/',           '/'],
            ['/../foo',     '/foo'],
            ['/../',        '/'],
            ['/foo/../bar', '/bar'],
            ['/foo/./bar',  '/foo/bar'],
        ];
    }

    /**
     * @dataProvider dataproviderNormalizePathInvalid
     */
    public function testNormalizePathInvalidThrow($input)
    {
        $this->expectException(RepositoryException::class);

        PathHelper::normalizePath($input);
    }

    /**
     * @dataProvider dataproviderNormalizePathInvalid
     */
    public function testNormalizePathInvalidNoThrow($input)
    {
        $this->assertFalse(PathHelper::normalizePath($input, true, false));
    }

    public static function dataproviderNormalizePathInvalid()
    {
        return [
            ['foo/bar'],
            ['bar'],
            ['/foo/bar/'],
            [''],
            [new stdClass()],
        ];
    }

    // absolutizePath tests

    /**
     * @dataProvider dataproviderAbsolutizePath
     */
    public function testAbsolutizePath($inputPath, $context, $outputPath)
    {
        $this->assertSame($outputPath, PathHelper::absolutizePath($inputPath, $context));
    }

    public static function dataproviderAbsolutizePath()
    {
        return [
            ['/../foo',    '/',    '/foo'],
            ['../',        '/',    '/'],
            ['../foo/bar', '/baz', '/foo/bar'],
            ['foo/./bar',  '/baz', '/baz/foo/bar'],
        ];
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     * @dataProvider dataproviderAbsolutizePathInvalid
     */
    public function testAbsolutizePathInvalidThrow($inputPath, $context, $target)
    {
        PathHelper::absolutizePath($inputPath, $context, $target);
    }

    /**
     * @dataProvider dataproviderAbsolutizePathInvalid
     */
    public function testAbsolutizePathInvalidNoThrow($inputPath, $context, $target)
    {
        $this->assertFalse(PathHelper::absolutizePath($inputPath, $context, $target, false));
    }

    public static function dataproviderAbsolutizePathInvalid()
    {
        return [
            ['', '/context', false],
            [null,    '/context',    false],
            ['foo',        null,    false],
            [new stdClass(), '/context', false],
            ['foo[2]',  '/bar', true],
        ];
    }

    // relativizePath tests

    /**
     * @dataProvider dataproviderRelativizePath
     */
    public function testRelativizePath($inputPath, $context, $outputPath)
    {
        $this->assertSame($outputPath, PathHelper::relativizePath($inputPath, $context));
    }

    public static function dataproviderRelativizePath()
    {
        return [
            ['/parent/path/child', '/parent', 'path/child'],
            ['/child', '/', 'child'],
        ];
    }

    /**
     * @dataProvider dataproviderRelativizePathInvalid
     */
    public function testRelativizePathInvalidThrow($inputPath, $context)
    {
        $this->expectException(RepositoryException::class);

        PathHelper::relativizePath($inputPath, $context);
    }

    /**
     * @dataProvider dataproviderRelativizePathInvalid
     */
    public function testRelativizePathInvalidNoThrow($inputPath, $context)
    {
        $this->assertFalse(PathHelper::relativizePath($inputPath, $context, false));
    }

    public static function dataproviderRelativizePathInvalid()
    {
        return [
            ['/path', '/context'],
            ['/parent', '/parent/child'],
        ];
    }

    // getParentPath tests

    /**
     * @dataProvider dataproviderParentPath
     */
    public function testGetParentPath($path, $parent)
    {
        $this->assertEquals($parent, PathHelper::getParentPath($path));
    }

    public function dataproviderParentPath()
    {
        return [
            ['/parent/child', '/parent'],
            ['/jcr:parent/ns:child', '/jcr:parent'],
            ['/child', '/'],
            ['/', '/'],
        ];
    }

    // getNodeName tests

    /**
     * @dataProvider dataproviderGetNodeName
     */
    public function testGetNodeName($path, $expected = null)
    {
        $this->assertEquals($expected, PathHelper::getNodeName($path));
    }

    public function dataproviderGetNodeName()
    {
        return [
            ['/parent/child', 'child'],
            ['/parent/ns:child', 'ns:child'],
            ['/', ''],
        ];
    }

    /**
     * @dataProvider dataproviderGetLocalNodeName
     */
    public function testGetLocalNodeName($path, $expected = null)
    {
        $this->assertEquals($expected, PathHelper::getLocalNodeName($path));
    }

    public function dataproviderGetLocalNodeName()
    {
        return [
            ['/parent/child', 'child'],
            ['/foo:child', 'child'],
            ['/parent/ns:child', 'child'],
            ['/ns:parent/child:foo', 'foo'],
            ['/', ''],
        ];
    }

    /**
     * @expectedExceptionMessage must be an absolute path
     */
    public function testGetNodeNameMustBeAbsolute()
    {
        $this->expectException(RepositoryException::class);

        PathHelper::getNodeName('foobar');
    }

    // getPathDepth tests

    /**
     * @dataProvider dataproviderPathDepth
     */
    public function testGetPathDepth($path, $depth)
    {
        $this->assertEquals($depth, PathHelper::getPathDepth($path));
    }

    public function dataproviderPathDepth()
    {
        return [
            ['/', 0],
            ['/foo', 1],
            ['/foo/bar', 2],
            ['/foo/bar/', 2],
        ];
    }
}

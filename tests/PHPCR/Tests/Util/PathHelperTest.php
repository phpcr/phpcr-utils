<?php

namespace PHPCR\Tests\Util;

use PHPCR\Util\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
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
        return array(
            array('/parent/child'),
            array('/'),
            array('/jcr:foo_/b-a/0^.txt'),
            array('/parent[7]/child'),
            array('/parent[7]/child', true), // index is allowed in destination parent path, only not in last element
            array('/parent[7]/child[3]'),
        );
    }

    /**
     * @dataProvider dataproviderInvalidAbsolutePaths
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertInvalidAbsolutePath($path, $destination = false)
    {
        PathHelper::assertValidAbsolutePath($path, $destination);
    }

    /**
     * @dataProvider dataproviderValidAbsolutePathsWithNamespaces
     */
    public function testAssertAbsolutePathNamespace($path)
    {
        PathHelper::assertValidAbsolutePath($path, false, true, array('jcr', 'nt'));
    }

    public function dataproviderValidAbsolutePathsWithNamespaces()
    {
        return array(
            array('/parent/child'),
            array('/jcr:localname'),
            array('/jcr:localname/test'),
            array('/jcr:localname/test/nt:node'),
            array('/jcr:localname/test/nt:node/bla'),
            array('/'),
            array('/jcr:foo_/b-a/0^.txt'),
            array('/parent[7]/child'),
            array('/jcr:localname[3]/test'),
        );
    }

    /**
     * @expectedException \PHPCR\NamespaceException
     * @expectedExceptionMessage invalidprefix and other-ns
     */
    public function testAssertInvalidNamespaceAbsolutePath()
    {
        PathHelper::assertValidAbsolutePath('/invalidprefix:localname/other-ns:test/invalidprefix:node/bla', false, true, array('jcr', 'nt'));
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
        return array(
            array('/parent/child[7]', true), // destination last element with index
            array('parent'), // not absolute
            array('/parent//child'),
            array('//'),
            array('/parent/../child'),
            array('/parent/./child'),
            array('/parent/child/'),
        );
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
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertInvalidLocalName($name)
    {
        PathHelper::assertValidLocalName($name);
    }

    public function dataproviderInvalidLocalNames()
    {
        return array(
            array('jcr:nodename'),
            array('/path'),
            array('.'),
            array('..')
        );
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
        return array(
            array('/',           '/'),
            array('/../foo',     '/foo'),
            array('/../',        '/'),
            array('/foo/../bar', '/bar'),
            array('/foo/./bar',  '/foo/bar'),
        );
    }

    /**
     * @dataProvider dataproviderNormalizePathInvalid
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNormalizePathInvalidThrow($input)
    {
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
        return array(
            array('foo/bar'),
            array('bar'),
            array('/foo/bar/'),
            array(''),
            array(new \stdClass()),
        );
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
        return array(
            array('/../foo',    '/',    '/foo'),
            array('../',        '/',    '/'),
            array('../foo/bar', '/baz', '/foo/bar'),
            array('foo/./bar',  '/baz', '/baz/foo/bar'),
        );
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
        return array(
            array('', '/context', false),
            array(null,    '/context',    false),
            array('foo',        null,    false),
            array(new \stdClass(), '/context', false),
            array('foo[2]',  '/bar', true),
        );
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
        return array(
            array('/parent/path/child', '/parent', 'path/child'),
            array('/child', '/', 'child'),
        );
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     * @dataProvider dataproviderRelativizePathInvalid
     */
    public function testRelativizePathInvalidThrow($inputPath, $context)
    {
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
        return array(
            array('/path', '/context'),
            array('/parent', '/parent/child'),
        );
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
        return array(
            array('/parent/child', '/parent'),
            array('/jcr:parent/ns:child', '/jcr:parent'),
            array('/child', '/'),
            array('/', '/'),
        );
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
        return array(
            array('/parent/child', 'child'),
            array('/parent/ns:child', 'ns:child'),
            array('/', ''),
        );
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
        return array(
            array('/parent/child', 'child'),
            array('/foo:child', 'child'),
            array('/parent/ns:child', 'child'),
            array('/ns:parent/child:foo', 'foo'),
            array('/', ''),
        );
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     * @expectedExceptionMessage must be an absolute path
     */
    public function testGetNodeNameMustBeAbsolute()
    {
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
        return array(
            array('/', 0),
            array('/foo', 1),
            array('/foo/bar', 2),
            array('/foo/bar/', 2),
        );
    }
}

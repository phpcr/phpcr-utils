<?php

namespace PHPCR\Tests\Util;

use PHPCR\Util\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
{
    // assertValidPath tests

    public function testAssertValidPath()
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath('/parent/child'));
    }

    public function testAssertValidPathRoot()
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath('/'));
    }

    public function testAssertValidPathNamespaced()
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath('/jcr:foo_/b-a/0^.txt'));
    }

    public function testAssertValidPathIndexed()
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath('/parent[7]/child'));
    }

    public function testAssertValidPathIndexedAtEnd()
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath('/parent[7]/child[3]'));
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidTargetPathNoIndex()
    {
        PathHelper::assertValidAbsolutePath('/parent/child[7]', true);
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidPathNotAbsolute()
    {
        PathHelper::assertValidAbsolutePath('parent');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidPathDouble()
    {
        PathHelper::assertValidAbsolutePath('/parent//child');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidPathParent()
    {
        PathHelper::assertValidAbsolutePath('/parent/../child');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidPathSelf()
    {
        PathHelper::assertValidAbsolutePath('/parent/./child');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidPathTrailing()
    {
        PathHelper::assertValidAbsolutePath('/parent/child/');
    }

    public function testAssertValidPathNoThrow()
    {
        $this->assertFalse(PathHelper::assertValidAbsolutePath('parent', false, false));
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
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidLocalNameNamespaced()
    {
        $this->assertTrue(PathHelper::assertValidLocalName('jcr:nodename'));
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidLocalNamePath()
    {
        $this->assertTrue(PathHelper::assertValidLocalName('/path'));
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidLocalNameSelf()
    {
        PathHelper::assertValidLocalName('.');
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testAssertValidLocalNameParent()
    {
        PathHelper::assertValidLocalName('..');
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

    // getParentPath tests

    public function testGetParentPath()
    {
        $this->assertEquals('/parent', PathHelper::getParentPath('/parent/child'));
    }

    public function testGetParentPathNamespaced()
    {
        $this->assertEquals('/jcr:parent', PathHelper::getParentPath('/jcr:parent/ns:child'));
    }

    public function testGetParentPathNodeAtRoot()
    {
        $this->assertEquals('/', PathHelper::getParentPath('/parent'));
    }

    public function testGetParentPathRoot()
    {
        $this->assertEquals('/', PathHelper::getParentPath('/'));
    }

    // getNodeName tests

    public function testGetNodeName()
    {
        $this->assertEquals('child', PathHelper::getNodeName('/parent/child'));
    }

    public function testGetNodeNameNamespaced()
    {
        $this->assertEquals('ns:child', PathHelper::getNodeName('/parent/ns:child'));
    }

    public function testGetNodeNameRoot()
    {
        $this->assertEquals('', PathHelper::getNodeName('/'));
    }

    public function testGetPathDepth()
    {
        $this->assertEquals(0, PathHelper::getPathDepth('/'));
        $this->assertEquals(1, PathHelper::getPathDepth('/foo'));
        $this->assertEquals(2, PathHelper::getPathDepth('/foo/bar'));
        $this->assertEquals(2, PathHelper::getPathDepth('/foo/bar/'));
    }
}

<?php

namespace PHPCR\Tests\Util;

use PHPCR\Util\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
{
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
            array('/../foo',       '/foo'),
            array('/../',           '/'),
            array('/foo/../bar',   '/bar'),
            array('/foo/./bar',    '/foo/bar'),
        );
    }

    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNormalizePathInvalid()
    {
        PathHelper::normalizePath('foo/bar');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNormalizePathTrailing()
    {
        PathHelper::normalizePath('/foo/bar/');
    }
    /**
     * @expectedException \PHPCR\RepositoryException
     */
    public function testNormalizePathEmpty()
    {
        PathHelper::normalizePath('');
    }

    public function testGetParentPath()
    {
        $this->assertEquals('/parent', PathHelper::getParentPath('/parent/child'));
    }
    public function testGetParentPathNamespaced()
    {
        $this->assertEquals('/jcr:parent', PathHelper::getParentPath('/jcr:parent/ns:child'));
    }
    public function testGetParentPathRoot()
    {
        $this->assertEquals('/', PathHelper::getParentPath('/'));
    }

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

}

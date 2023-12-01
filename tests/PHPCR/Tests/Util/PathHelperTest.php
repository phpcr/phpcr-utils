<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util;

use PHPCR\NamespaceException;
use PHPCR\RepositoryException;
use PHPCR\Util\PathHelper;
use PHPUnit\Framework\TestCase;

class PathHelperTest extends TestCase
{
    // assertValidPath tests

    /**
     * @dataProvider dataproviderValidAbsolutePaths
     */
    public function testAssertValidAbsolutePath(string $path, bool $destination = false): void
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath($path, $destination));
    }

    /**
     * @return array<array{0: string, 1?: true}>
     */
    public static function dataproviderValidAbsolutePaths(): array
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
    public function testAssertInvalidAbsolutePath(string $path, bool $destination = false): void
    {
        $this->expectException(RepositoryException::class);

        PathHelper::assertValidAbsolutePath($path, $destination);
    }

    /**
     * @dataProvider dataproviderValidAbsolutePathsWithNamespaces
     */
    public function testAssertAbsolutePathNamespace(string $path): void
    {
        $this->assertTrue(PathHelper::assertValidAbsolutePath($path, false, true, ['jcr', 'nt']));
    }

    /**
     * @return array<array{0: string}>
     */
    public function dataproviderValidAbsolutePathsWithNamespaces(): array
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

    public function testAssertInvalidNamespaceAbsolutePath(): void
    {
        $this->expectException(NamespaceException::class);
        $this->expectExceptionMessage('invalidprefix and other-ns');

        PathHelper::assertValidAbsolutePath('/invalidprefix:localname/other-ns:test/invalidprefix:node/bla', false, true, ['jcr', 'nt']);
    }

    /**
     * @dataProvider dataproviderInvalidAbsolutePaths
     */
    public function testAssertInvalidAbsolutePathNoThrow(string $path, bool $destination = false): void
    {
        $this->assertFalse(PathHelper::assertValidAbsolutePath($path, $destination, false));
    }

    /**
     * @return array<array{0: string, 1?: true}>
     */
    public function dataproviderInvalidAbsolutePaths(): array
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

    public function testAssertValidLocalName(): void
    {
        $this->assertTrue(PathHelper::assertValidLocalName('nodename'));
    }

    public function testAssertValidLocalNameRootnode(): void
    {
        $this->assertTrue(PathHelper::assertValidLocalName(''));
    }

    /**
     * @dataProvider dataproviderInvalidLocalNames
     */
    public function testAssertInvalidLocalName(string $name): void
    {
        $this->expectException(RepositoryException::class);

        PathHelper::assertValidLocalName($name);
    }

    /**
     * @return array<array{0: string}>
     */
    public static function dataproviderInvalidLocalNames(): array
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
    public function testNormalizePath(string $inputPath, string $outputPath): void
    {
        $this->assertSame($outputPath, PathHelper::normalizePath($inputPath));
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public static function dataproviderNormalizePath(): array
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
    public function testNormalizePathInvalidThrow(string $input): void
    {
        $this->expectException(RepositoryException::class);

        PathHelper::normalizePath($input);
    }

    /**
     * @dataProvider dataproviderNormalizePathInvalid
     */
    public function testNormalizePathInvalidNoThrow(string $input): void
    {
        $this->assertFalse(PathHelper::normalizePath($input, true, false));
    }

    /**
     * @return array<array{0: string}>
     */
    public static function dataproviderNormalizePathInvalid(): array
    {
        return [
            ['foo/bar'],
            ['bar'],
            ['/foo/bar/'],
            [''],
            ['//'],
        ];
    }

    // absolutizePath tests

    /**
     * @dataProvider dataproviderAbsolutizePath
     */
    public function testAbsolutizePath(string $inputPath, string $context, string $outputPath): void
    {
        $this->assertSame($outputPath, PathHelper::absolutizePath($inputPath, $context));
    }

    /**
     * @return array<array{0: string, 1: string, 2: string}>
     */
    public static function dataproviderAbsolutizePath(): array
    {
        return [
            ['/../foo',    '/',    '/foo'],
            ['../',        '/',    '/'],
            ['../foo/bar', '/baz', '/foo/bar'],
            ['foo/./bar',  '/baz', '/baz/foo/bar'],
        ];
    }

    /**
     * @dataProvider dataproviderAbsolutizePathInvalid
     */
    public function testAbsolutizePathInvalidThrow(string $inputPath, string $context, bool $target): void
    {
        $this->expectException(RepositoryException::class);
        PathHelper::absolutizePath($inputPath, $context, $target);
    }

    /**
     * @dataProvider dataproviderAbsolutizePathInvalid
     */
    public function testAbsolutizePathInvalidNoThrow(string $inputPath, string $context, bool $target): void
    {
        $this->assertFalse(PathHelper::absolutizePath($inputPath, $context, $target, false));
    }

    /**
     * @return array<array{0: string, 1: string, 2: bool}>
     */
    public static function dataproviderAbsolutizePathInvalid(): array
    {
        return [
            ['', '/context', false],
            ['//',    '/context',    false],
            ['foo',        '//',    false],
            ['foo[2]',  '/bar', true],
        ];
    }

    // relativizePath tests

    /**
     * @dataProvider dataproviderRelativizePath
     */
    public function testRelativizePath(string $inputPath, string $context, string $outputPath): void
    {
        $this->assertSame($outputPath, PathHelper::relativizePath($inputPath, $context));
    }

    /**
     * @return array<array{0: string, 1: string, 2: string}>
     */
    public static function dataproviderRelativizePath(): array
    {
        return [
            ['/parent/path/child', '/parent', 'path/child'],
            ['/child', '/', 'child'],
        ];
    }

    /**
     * @dataProvider dataproviderRelativizePathInvalid
     */
    public function testRelativizePathInvalidThrow(string $inputPath, string $context): void
    {
        $this->expectException(RepositoryException::class);

        PathHelper::relativizePath($inputPath, $context);
    }

    /**
     * @dataProvider dataproviderRelativizePathInvalid
     */
    public function testRelativizePathInvalidNoThrow(string $inputPath, string $context): void
    {
        $this->assertFalse(PathHelper::relativizePath($inputPath, $context, false));
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public static function dataproviderRelativizePathInvalid(): array
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
    public function testGetParentPath(string $path, string $parent): void
    {
        $this->assertEquals($parent, PathHelper::getParentPath($path));
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function dataproviderParentPath(): array
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
    public function testGetNodeName(string $path, string $expected): void
    {
        $this->assertEquals($expected, PathHelper::getNodeName($path));
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function dataproviderGetNodeName(): array
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
    public function testGetLocalNodeName(string $path, string $expected): void
    {
        $this->assertEquals($expected, PathHelper::getLocalNodeName($path));
    }

    /**
     * @return array<array{0: string, 1: string}>
     */
    public function dataproviderGetLocalNodeName(): array
    {
        return [
            ['/parent/child', 'child'],
            ['/foo:child', 'child'],
            ['/parent/ns:child', 'child'],
            ['/ns:parent/child:foo', 'foo'],
            ['/', ''],
        ];
    }

    public function testGetNodeNameMustBeAbsolute(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('must be an absolute path');

        PathHelper::getNodeName('foobar');
    }

    // getPathDepth tests

    /**
     * @dataProvider dataproviderPathDepth
     */
    public function testGetPathDepth(string $path, int $depth): void
    {
        $this->assertEquals($depth, PathHelper::getPathDepth($path));
    }

    /**
     * @return array<array{0: string, 1: int}>
     */
    public function dataproviderPathDepth(): array
    {
        return [
            ['/', 0],
            ['/foo', 1],
            ['/foo/bar', 2],
            ['/foo/bar/', 2],
        ];
    }
}

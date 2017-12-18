<?php

namespace PHPCR\Tests\Util;

use DateTime;
use PHPCR\PropertyType;
use PHPCR\RepositoryException;
use PHPCR\Tests\Stubs\MockNode;
use PHPCR\Util\ValueConverter;
use PHPCR\ValueFormatException;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../Stubs/MockNode.php';

/**
 * A test for the PHPCR\PropertyType class.
 */
class ValueConverterTest extends TestCase
{
    /**
     * @var ValueConverter
     */
    private $valueConverter;

    public function setUp()
    {
        $this->valueConverter = new ValueConverter();
    }

    public function dataConversionMatrix()
    {
        $stream = fopen('php://memory', '+rw');
        fwrite($stream, 'test string');
        rewind($stream);

        $dateStream = fopen('php://memory', '+rw');
        fwrite($dateStream, '17.12.2010  GMT');
        rewind($dateStream);

        $numberStream = fopen('php://memory', '+rw');
        fwrite($numberStream, '123.456');
        rewind($numberStream);

        $nameStream = fopen('php://memory', '+rw');
        fwrite($nameStream, 'test');
        rewind($nameStream);

        $uuidStream = fopen('php://memory', '+rw');
        fwrite($uuidStream, '38b7cf18-c417-477a-af0b-c1e92a290c9a');
        rewind($uuidStream);

        $datetimeLong = new DateTime();
        $datetimeLong->setTimestamp(123);

        $nodeMock = $this->createMock(MockNode::class);
        $nodeMock
            ->expects($this->any())
            ->method('getIdentifier')
            ->will($this->returnValue('38b7cf18-c417-477a-af0b-c1e92a290c9a'));

        $nodeMock
            ->expects($this->any())
            ->method('isNodeType')
            ->with('mix:referenceable')
            ->will($this->returnValue(true));

        return [
            // String to...
            ['test string', PropertyType::STRING, 'test string', PropertyType::STRING],
            ['test string', PropertyType::STRING, 0, PropertyType::LONG],
            ['378.37', PropertyType::STRING, 378, PropertyType::LONG],
            ['test string', PropertyType::STRING, 0.0, PropertyType::DOUBLE],
            ['249.39', PropertyType::STRING, 249.39, PropertyType::DOUBLE],
            ['test string', PropertyType::STRING, null, PropertyType::DATE],
            ['17.12.2010 GMT', PropertyType::STRING, new DateTime('17.12.2010 GMT'), PropertyType::DATE],
            ['test string', PropertyType::STRING, true, PropertyType::BOOLEAN],
            ['false', PropertyType::STRING, true, PropertyType::BOOLEAN],
            ['', PropertyType::STRING, false, PropertyType::BOOLEAN],
            // TODO: check NAME may not have spaces ['test string', PropertyType::STRING, null, PropertyType::NAME],
            ['test', PropertyType::STRING, 'test', PropertyType::NAME],
            // TODO: check PATH may not have spaces ['test string', PropertyType::STRING, null, PropertyType::PATH],
            ['../the/node', PropertyType::STRING, '../the/node', PropertyType::PATH],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::STRING, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            // TODO: should we move UUIDHelper to phpcr so we can check in PropertyType? ['test string', PropertyType::STRING, null, PropertyType::REFERENCE],
            ['', PropertyType::STRING, null, PropertyType::REFERENCE],
            ['http://phpcr.github.com/', PropertyType::STRING, 'http://phpcr.github.com/', PropertyType::URI],
            ['test string', PropertyType::STRING, 'test string', PropertyType::DECIMAL], // up to the decimal functions to validate

            // Stream to...
            [$stream, PropertyType::BINARY, 'test string', PropertyType::STRING],
            [$stream, PropertyType::BINARY, 0, PropertyType::LONG],
            [$numberStream, PropertyType::BINARY, 123, PropertyType::LONG],
            [$stream, PropertyType::BINARY, 0.0, PropertyType::DOUBLE],
            [$numberStream, PropertyType::BINARY, 123.456, PropertyType::DOUBLE],
            [$stream, PropertyType::BINARY, null, PropertyType::DATE],
            [$dateStream, PropertyType::BINARY, new DateTime('17.12.2010 GMT'), PropertyType::DATE],
            [$stream, PropertyType::BINARY, true, PropertyType::BOOLEAN],
            // TODO: check NAME may not have spaces [$stream, PropertyType::BINARY, null, PropertyType::NAME],
            [$nameStream, PropertyType::BINARY, 'test', PropertyType::NAME],
            // TODO: check PATH may not have spaces [$stream, PropertyType::BINARY, null, PropertyType::PATH],
            // TODO: should we move UUIDHelper to phpcr so we can check in PropertyType? [$stream, PropertyType::STRING, null, PropertyType::REFERENCE],
            [$uuidStream, PropertyType::BINARY, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            [$stream, PropertyType::BINARY, 'test string', PropertyType::DECIMAL], // up to the decimal functions to validate

            // Invalid stream resource
            [$stream, PropertyType::DECIMAL, null, PropertyType::STRING],

            // Long to...
            [123, PropertyType::LONG, '123', PropertyType::STRING],
            [123, PropertyType::LONG, 123, PropertyType::LONG],
            [123, PropertyType::LONG, 123.0, PropertyType::DOUBLE],
            [123, PropertyType::LONG, $datetimeLong, PropertyType::DATE],
            [123, PropertyType::LONG, true, PropertyType::BOOLEAN],
            [0, PropertyType::LONG, false, PropertyType::BOOLEAN],
            [123, PropertyType::LONG, null, PropertyType::NAME],
            [123, PropertyType::LONG, null, PropertyType::PATH],
            [123, PropertyType::LONG, null, PropertyType::REFERENCE],
            [123, PropertyType::LONG, null, PropertyType::URI],
            [123, PropertyType::LONG, '123', PropertyType::DECIMAL],

            // Double to...
            [123.1, PropertyType::DOUBLE, '123.1', PropertyType::STRING],
            [123.1, PropertyType::DOUBLE, 123, PropertyType::LONG],
            [123.1, PropertyType::DOUBLE, 123.1, PropertyType::DOUBLE],
            [123.1, PropertyType::DOUBLE, $datetimeLong, PropertyType::DATE],
            [123.1, PropertyType::DOUBLE, true, PropertyType::BOOLEAN],
            [0.0, PropertyType::DOUBLE, false, PropertyType::BOOLEAN],
            [123.1, PropertyType::DOUBLE, null, PropertyType::NAME],
            [123.1, PropertyType::DOUBLE, null, PropertyType::PATH],
            [123.1, PropertyType::DOUBLE, null, PropertyType::REFERENCE],
            [123.1, PropertyType::DOUBLE, null, PropertyType::URI],
            [123.1, PropertyType::DOUBLE, '123.1', PropertyType::DECIMAL],

            // Date to...
            [$datetimeLong, PropertyType::DATE, $datetimeLong->format('Y-m-d\TH:i:s.').substr($datetimeLong->format('u'), 0, 3).$datetimeLong->format('P'), PropertyType::STRING],
            [$datetimeLong, PropertyType::DATE, 123, PropertyType::LONG],
            [$datetimeLong, PropertyType::DATE, 123.0, PropertyType::DOUBLE],
            [$datetimeLong, PropertyType::DATE, $datetimeLong, PropertyType::DATE],
            [$datetimeLong, PropertyType::DATE, true, PropertyType::BOOLEAN],
            [$datetimeLong, PropertyType::DATE, null, PropertyType::NAME],
            [$datetimeLong, PropertyType::DATE, null, PropertyType::PATH],
            [$datetimeLong, PropertyType::DATE, null, PropertyType::REFERENCE],
            [$datetimeLong, PropertyType::DATE, null, PropertyType::URI],
            [$datetimeLong, PropertyType::DATE, '123', PropertyType::DECIMAL],

            // Boolean to...
            [true, PropertyType::BOOLEAN, '1', PropertyType::STRING],
            [false, PropertyType::BOOLEAN, '', PropertyType::STRING],
            [true, PropertyType::BOOLEAN, null, PropertyType::DATE],
            [true, PropertyType::BOOLEAN, 1, PropertyType::LONG],
            [true, PropertyType::BOOLEAN, 1.0, PropertyType::DOUBLE],
            [true, PropertyType::BOOLEAN, true, PropertyType::BOOLEAN],
            [true, PropertyType::BOOLEAN, null, PropertyType::NAME],
            [true, PropertyType::BOOLEAN, null, PropertyType::PATH],
            [true, PropertyType::BOOLEAN, null, PropertyType::REFERENCE],
            [true, PropertyType::BOOLEAN, null, PropertyType::URI],
            [true, PropertyType::BOOLEAN, '1', PropertyType::DECIMAL],
            [false, PropertyType::BOOLEAN, '', PropertyType::DECIMAL],

            // Name to...
            ['name', PropertyType::NAME, 'name', PropertyType::STRING],
            ['name', PropertyType::NAME, null, PropertyType::DATE],
            ['name', PropertyType::NAME, null, PropertyType::LONG],
            ['name', PropertyType::NAME, null, PropertyType::DOUBLE],
            ['name', PropertyType::NAME, null, PropertyType::BOOLEAN],
            ['name', PropertyType::NAME, 'name', PropertyType::NAME],
            ['name', PropertyType::NAME, 'name', PropertyType::PATH],
            ['name', PropertyType::NAME, null, PropertyType::REFERENCE],
            ['name', PropertyType::NAME, '../name', PropertyType::URI],
            ['näme', PropertyType::NAME, '../n%C3%A4me', PropertyType::URI],
            ['name', PropertyType::NAME, null, PropertyType::DECIMAL],

            // Path to...
            ['../test/path', PropertyType::PATH, '../test/path', PropertyType::STRING],
            ['../test/path', PropertyType::PATH, null, PropertyType::DATE],
            ['../test/path', PropertyType::PATH, null, PropertyType::LONG],
            ['../test/path', PropertyType::PATH, null, PropertyType::DOUBLE],
            ['../test/path', PropertyType::PATH, null, PropertyType::BOOLEAN],
            // TODO: fix ['../test/path', PropertyType::PATH, null, PropertyType::NAME],
            // TODO: fix ['./path', PropertyType::PATH, 'path', PropertyType::NAME],
            ['../test/path', PropertyType::PATH, '../test/path', PropertyType::PATH],
            ['../test/path', PropertyType::PATH, null, PropertyType::REFERENCE],
            ['../test/path', PropertyType::PATH, '../test/path', PropertyType::URI],
            ['../test/päth', PropertyType::PATH, '../test/p%C3%A4th', PropertyType::URI],
            ['test', PropertyType::PATH, './test', PropertyType::URI],
            ['../test/path', PropertyType::PATH, null, PropertyType::DECIMAL],

            // Reference to...
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::STRING],
            [$nodeMock, PropertyType::REFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::STRING],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            [$nodeMock, PropertyType::REFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::DATE],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::DATE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::LONG],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::LONG],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::DOUBLE],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::DOUBLE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::BOOLEAN],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::BOOLEAN],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::NAME],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::NAME],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::PATH],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::PATH],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::URI],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::URI],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE, null, PropertyType::DECIMAL],
            [$nodeMock, PropertyType::REFERENCE, null, PropertyType::DECIMAL],

            [$this, PropertyType::REFERENCE, null, PropertyType::STRING],
            [$this, PropertyType::REFERENCE, null, PropertyType::BINARY],
            [$this, PropertyType::REFERENCE, null, PropertyType::DATE],
            [$this, PropertyType::REFERENCE, null, PropertyType::LONG],
            [$this, PropertyType::REFERENCE, null, PropertyType::DOUBLE],
            [$this, PropertyType::REFERENCE, null, PropertyType::BOOLEAN],
            [$this, PropertyType::REFERENCE, null, PropertyType::NAME],
            [$this, PropertyType::REFERENCE, null, PropertyType::PATH],
            [$this, PropertyType::REFERENCE, null, PropertyType::URI],
            [$this, PropertyType::REFERENCE, null, PropertyType::DECIMAL],

            // Weak to...
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::STRING],
            [$nodeMock, PropertyType::WEAKREFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::STRING],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            [$nodeMock, PropertyType::WEAKREFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::DATE],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::DATE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::LONG],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::LONG],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::DOUBLE],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::DOUBLE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::BOOLEAN],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::BOOLEAN],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::NAME],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::NAME],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::PATH],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::PATH],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::URI],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::URI],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, '38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::REFERENCE],
            ['38b7cf18-c417-477a-af0b-c1e92a290c9a', PropertyType::WEAKREFERENCE, null, PropertyType::DECIMAL],
            [$nodeMock, PropertyType::WEAKREFERENCE, null, PropertyType::DECIMAL],

            // uri to...
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, 'http://phpcr.githbub.com/doc/html', PropertyType::STRING],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::DATE],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::LONG],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::DOUBLE],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::BOOLEAN],
            // TODO: fix ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::NAME],
            // TODO: fix ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::PATH],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::REFERENCE],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, 'http://phpcr.githbub.com/doc/html', PropertyType::URI],
            ['http://phpcr.githbub.com/doc/html', PropertyType::URI, null, PropertyType::DECIMAL],

            // decimal to...
            ['123.4', PropertyType::DECIMAL, '123.4', PropertyType::STRING],
            ['123.4', PropertyType::DECIMAL, $datetimeLong, PropertyType::DATE],
            ['123.4', PropertyType::DECIMAL, 123, PropertyType::LONG],
            ['123.4', PropertyType::DECIMAL, 123.4, PropertyType::DOUBLE],
            ['123.4', PropertyType::DECIMAL, true, PropertyType::BOOLEAN],
            ['0', PropertyType::DECIMAL, false, PropertyType::BOOLEAN],
            ['123.4', PropertyType::DECIMAL, null, PropertyType::NAME],
            ['123.4', PropertyType::DECIMAL, null, PropertyType::PATH],
            ['123.4', PropertyType::DECIMAL, null, PropertyType::URI],
            ['123.4', PropertyType::DECIMAL, null, PropertyType::REFERENCE],
            ['123.4', PropertyType::DECIMAL, '123.4', PropertyType::DECIMAL],
        ];
    }

    /**
     * Skip binary target as its a special case.
     *
     * @param mixed $value
     * @param int   $srcType PropertyType constant to convert from
     * @param $expected
     * @param $targetType
     *
     * @dataProvider dataConversionMatrix
     */
    public function testConvertType($value, $srcType, $expected, $targetType)
    {
        if (null === $expected) {
            try {
                $this->valueConverter->convertType($value, $targetType, $srcType);
                $this->fail('Expected that this conversion would throw an exception');
            } catch (ValueFormatException $e) {
                // expected
                $this->assertTrue(true); // make it assert something
            }
        } else {
            if ($expected instanceof DateTime) {
                $result = $this->valueConverter->convertType($value, $targetType, $srcType);
                $this->assertInstanceOf(DateTime::class, $result);
                $this->assertEquals($expected->getTimestamp(), $result->getTimestamp());
            } else {
                $this->assertSame($expected, $this->valueConverter->convertType($value, $targetType, $srcType));
            }
        }
    }

    public function testConvertTypeToBinary()
    {
        $stream = $this->valueConverter->convertType('test string', PropertyType::BINARY);
        $this->assertInternalType('resource', $stream);
        $string = stream_get_contents($stream);
        $this->assertEquals('test string', $string);

        $stream = $this->valueConverter->convertType('test string', PropertyType::BINARY, PropertyType::BINARY);
        $this->assertInternalType('resource', $stream);
        $string = stream_get_contents($stream);
        $this->assertEquals('test string', $string);

        $date = new DateTime('20.12.2012');
        $stream = $this->valueConverter->convertType($date, PropertyType::BINARY);
        $this->assertInternalType('resource', $stream);
        $string = stream_get_contents($stream);
        $readDate = new DateTime($string);
        $this->assertEquals($date->getTimestamp(), $readDate->getTimestamp());

        $stream = fopen('php://memory', '+rw');
        fwrite($stream, 'test string');
        rewind($stream);

        $result = $this->valueConverter->convertType($stream, PropertyType::BINARY, PropertyType::BINARY);
        $string = stream_get_contents($result);
        $this->assertEquals('test string', $string);
        // if conversion to string works, should be fine for all others
    }

    public function testConvertTypeArray()
    {
        $result = $this->valueConverter->convertType(['2012-01-10', '2012-02-12'],
            PropertyType::DATE,
            PropertyType::STRING);
        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);

        $this->assertInstanceOf(DateTime::class, $result[0]);
        $this->assertInstanceOf(DateTime::class, $result[1]);

        $this->assertEquals('2012-01-10', $result[0]->format('Y-m-d'));
        $this->assertEquals('2012-02-12', $result[1]->format('Y-m-d'));

        $result = $this->valueConverter->convertType([], PropertyType::STRING, PropertyType::NAME);
        $this->assertEquals([], $result);
    }

    public function testConvertTypeAutodetect()
    {
        $date = new DateTime('2012-10-10');
        $result = $this->valueConverter->convertType($date, PropertyType::STRING);
        $result = new DateTime($result);
        $this->assertEquals($date->getTimestamp(), $result->getTimestamp());

        $result = $this->valueConverter->convertType('2012-03-13T21:00:55.000+01:00', PropertyType::DATE);
        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals(1331668855, $result->getTimestamp());
    }

    public function testConvertTypeArrayInvalid()
    {
        $this->expectException(ValueFormatException::class);

        $this->valueConverter->convertType(['a', 'b', 'c'], PropertyType::NAME, PropertyType::REFERENCE);
    }

    public function testConvertInvalidString()
    {
        $this->expectException(ValueFormatException::class);

        $this->valueConverter->convertType($this, PropertyType::STRING);
    }

    public function testConvertInvalidBinary()
    {
        $this->expectException(ValueFormatException::class);

        $this->valueConverter->convertType($this, PropertyType::BINARY);
    }

    public function testConvertInvalidDate()
    {
        $this->expectException(ValueFormatException::class);

        $this->valueConverter->convertType($this, PropertyType::DATE);
    }

    public function testConvertNewNode()
    {
        $this->expectException(ValueFormatException::class);

        $nodeMock = $this->createMock(MockNode::class);
        $nodeMock
            ->expects($this->never())
            ->method('isNew')
            ->will($this->returnValue(true));
        $this->valueConverter->convertType($nodeMock, PropertyType::STRING);
    }

    public function testConvertNonRefNode()
    {
        $this->expectException(ValueFormatException::class);

        $nodeMock = $this->createMock(MockNode::class);
        $nodeMock
            ->expects($this->never())
            ->method('isNew')
            ->will($this->returnValue(false));
        $nodeMock
            ->expects($this->once())
            ->method('isNodeType')
            ->with('mix:referenceable')
            ->will($this->returnValue(false));
        $this->valueConverter->convertType($nodeMock, PropertyType::STRING);
    }

    public function dataDateTargetType()
    {
        return [
            [PropertyType::STRING],
            [PropertyType::LONG],
            [PropertyType::DOUBLE],
        ];
    }

    /**
     * Check if the util will survive a broken implementation.
     *
     * @dataProvider dataDateTargetType
     */
    public function testConvertInvalidDateValue($targettype)
    {
        $this->expectException(RepositoryException::class);

        $this->valueConverter->convertType('', $targettype, PropertyType::DATE);
    }

    public function testConvertTypeInvalidTarget()
    {
        $this->expectException(ValueFormatException::class);

        $this->valueConverter->convertType('test', PropertyType::UNDEFINED);
    }
}

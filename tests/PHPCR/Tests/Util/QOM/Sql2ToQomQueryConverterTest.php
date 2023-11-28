<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Query\InvalidQueryException;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;
use PHPCR\Util\ValueConverter;
use PHPUnit\Framework\TestCase;

class Sql2ToQomQueryConverterTest extends TestCase
{
    /**
     * @var QueryObjectModelFactoryInterface
     */
    protected $qomFactory;

    /**
     * @var ValueConverter
     */
    protected $valueConverter;

    /**
     * @var Sql2ToQomQueryConverter
     */
    protected $converter;

    public function setUp(): void
    {
        $this->qomFactory = $this->createMock(QueryObjectModelFactoryInterface::class);
        $this->valueConverter = $this->createMock(ValueConverter::class);
        $this->converter = new Sql2ToQomQueryConverter($this->qomFactory, $this->valueConverter);
    }

    public function testInvalid(): void
    {
        $this->expectException(InvalidQueryException::class);
        $this->expectExceptionMessage('Error parsing query');

        $this->converter->parse('SELECTING WITH AN INVALID QUERY');
    }
}

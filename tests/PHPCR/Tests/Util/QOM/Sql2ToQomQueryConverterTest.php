<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;
use PHPCR\Util\ValueConverter;

class Sql2ToQomQueryConverterTest extends \PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->qomFactory = $this->getMock('PHPCR\Query\QOM\QueryObjectModelFactoryInterface');
        $this->valueConverter = $this->getMock('PHPCR\Util\ValueConverter');
        $this->converter = new Sql2ToQomQueryConverter($this->qomFactory, $this->valueConverter);
    }

    /**
     * @expectedException \PHPCR\Query\InvalidQueryException
     * @expectedExceptionMessage Error parsing query
     */
    public function testInvalid()
    {
        $this->converter->parse('SELECTING WITH AN INVALID QUERY');
    }
}

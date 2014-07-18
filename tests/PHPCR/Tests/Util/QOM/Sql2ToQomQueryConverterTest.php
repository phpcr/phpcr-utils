<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Util\QOM\Sql2ToQomQueryConverter;

class Sql2ToQomQueryConverterTest extends \PHPUnit_Framework_TestCase
{
    protected $qomFactory;
    protected $valueConverter;

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

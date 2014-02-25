<?php

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Util\QOM\QueryBuilder;
use PHPCR\Util\QOM\Sql2Scanner;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;

class Sql2ToQomQueryConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryObjectModelFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $qf;

    public function setUp()
    {
        $this->qf = $this->getMock('\PHPCR\Query\QOM\QueryObjectModelFactoryInterface');
    }

    public function literals()
    {
        return array(
            array('".."', '..'),
            array('"foo & bar&baz"', 'foo & bar&baz'),
        );
    }

    /**
     * @dataProvider literals
     */
    public function testParseLiteral($query, $parsed)
    {
        $this->qf->expects($this->once())
            ->method('literal')
            ->with($parsed)
            ->will($this->returnValue(true))
        ;

        $converter = new Sql2ToQomQueryConverter($this->qf);
        $scanner = new Sql2Scanner($query);
        $ref = new \ReflectionClass('PHPCR\Util\QOM\Sql2ToQomQueryConverter');
        $scannerProp = $ref->getProperty('scanner');
        $scannerProp->setAccessible(true);
        $scannerProp->setValue($converter, $scanner);
        $parseLiteral = $ref->getMethod('parseLiteral');
        $parseLiteral->setAccessible(true);
        $this->assertTrue($parseLiteral->invoke($converter));
    }
}

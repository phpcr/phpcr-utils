<?php
namespace PHPCR\Tests\Util\QOM;


use Jackalope\Factory;
use Jackalope\Query\QOM\QueryObjectModelFactory;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Util\QOM\Sql2ToQomQueryConverter;

class Sql2ToQomQueryConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  QueryObjectModelFactoryInterface */
    protected $factory;

    /** @var  Sql2ToQomQueryConverter */
    protected $converter;

    public function setUp()
    {
        $this->factory = new QueryObjectModelFactory(new Factory());
        $this->converter = new Sql2ToQomQueryConverter($this->factory);
    }

    public function testPropertyComparison()
    {
        $qom = $this->converter->parse('
            SELECT data
            FROM [nt:unstructured] AS data
            WHERE data.property = "String with spaces"
        ');

        $this->assertInstanceOf('PHPCR\Query\QOM\ComparisonInterface', $qom->getConstraint());
        $this->assertInstanceOf('PHPCR\Query\QOM\PropertyValueInterface', $qom->getConstraint()->getOperand1());
        $this->assertInstanceOf('PHPCR\Query\QOM\LiteralInterface', $qom->getConstraint()->getOperand2());

        $this->assertEquals('property', $qom->getConstraint()->getOperand1()->getPropertyName());
        $this->assertEquals('String with spaces', $qom->getConstraint()->getOperand2()->getLiteralValue());
    }

    public function testPropertyExistence()
    {
        $qom = $this->converter->parse('
            SELECT data
            FROM [nt:unstructured] AS data
            WHERE data.property IS NULL
        ');

        $this->assertInstanceOf('PHPCR\Query\QOM\NotInterface', $qom->getConstraint());
        $this->assertInstanceOf('PHPCR\Query\QOM\PropertyExistenceInterface', $qom->getConstraint()->getConstraint());

        $qom = $this->converter->parse('
            SELECT data
            FROM [nt:unstructured] AS data
            WHERE data.property IS NOT NULL
        ');

        $this->assertInstanceOf('PHPCR\Query\QOM\PropertyExistenceInterface', $qom->getConstraint());
    }

    public function testComposedConstraint()
    {
        $qom = $this->converter->parse('
            SELECT data
            FROM [nt:unstructured] AS data
            WHERE data.property = "foo"
               OR data.property = "bar"
        ');

        $this->assertInstanceOf('PHPCR\Query\QOM\OrInterface', $qom->getConstraint());
        $this->assertInstanceOf('PHPCR\Query\QOM\ComparisonInterface', $qom->getConstraint()->getConstraint1());
        $this->assertInstanceOf('PHPCR\Query\QOM\ComparisonInterface', $qom->getConstraint()->getConstraint2());

        $this->assertInstanceOf('PHPCR\Query\QOM\PropertyValueInterface', $qom->getConstraint()->getConstraint1()->getOperand1());
        $this->assertInstanceOf('PHPCR\Query\QOM\LiteralInterface', $qom->getConstraint()->getConstraint1()->getOperand2());
    }

    public function testJoinConstraint()
    {
        $qom = $this->converter->parse('
            SELECT data
            FROM [nt:unstructured] AS data
            JOIN [nt:folder] AS folder ON data.folder = folder.[jcr:uuid]
        ');

        $this->assertInstanceOf('PHPCR\Query\QOM\JoinInterface', $qom->getSource());
        $this->assertInstanceOf('PHPCR\Query\QOM\EquiJoinConditionInterface', $qom->getSource()->getJoinCondition());
        $this->assertEquals(\PHPCR\Query\QOM\QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_INNER, $qom->getSource()->getJoinType());
    }
}
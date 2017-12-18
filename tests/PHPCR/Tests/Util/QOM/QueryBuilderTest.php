<?php

namespace PHPCR\Tests\Util\QOM;

use InvalidArgumentException;
use PHPCR\Query\QOM\ConstraintInterface;
use PHPCR\Query\QOM\DynamicOperandInterface;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Query\QOM\QueryObjectModelInterface;
use PHPCR\Query\QOM\SameNodeJoinConditionInterface;
use PHPCR\Query\QOM\SourceInterface;
use PHPCR\Util\QOM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use RuntimeException;

class QueryBuilderTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|QueryObjectModelFactoryInterface
     */
    protected $qf;

    public function setUp()
    {
        $this->qf = $this->getMockBuilder(QueryObjectModelFactoryInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();
    }

    public function testSetFirstResult()
    {
        $qb = new QueryBuilder($this->qf);
        $qb->setFirstResult(15);
        $this->assertEquals(15, $qb->getFirstResult());
    }

    public function testSetMaxResults()
    {
        $qb = new QueryBuilder($this->qf);
        $qb->setMaxResults(15);
        $this->assertEquals(15, $qb->getMaxResults());
    }

    /**
     * @return DynamicOperandInterface
     */
    private function createDynamicOperandMock()
    {
        /** @var DynamicOperandInterface $dynamicOperand */
        $dynamicOperand = $this->getMockBuilder(DynamicOperandInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();

        return $dynamicOperand;
    }

    public function testAddOrderBy()
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'ASC');
        $qb->addOrderBy($dynamicOperand, 'DESC');
        $this->assertCount(2, $qb->getOrderings());
        $orderings = $qb->getOrderings();
    }

    public function testAddOrderByLowercase()
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'asc');
        $qb->addOrderBy($dynamicOperand, 'desc');
        $this->assertCount(2, $qb->getOrderings());
        $orderings = $qb->getOrderings();
    }

    public function testAddOrderByInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'FOO');
    }

    public function testOrderBy()
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->orderBy($dynamicOperand, 'ASC');
        $qb->orderBy($dynamicOperand, 'ASC');
        $this->assertCount(1, $qb->getOrderings());
    }

    public function testOrderAscending()
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $this->qf->expects($this->once())
                 ->method('ascending')
                 ->with($this->equalTo($dynamicOperand));

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'ASC');
    }

    public function testOrderDescending()
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $this->qf->expects($this->once())
                 ->method('descending')
                 ->with($this->equalTo($dynamicOperand));

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'DESC');
    }

    public function testOrderAscendingIsDefault()
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $this->qf->expects($this->once())
                 ->method('ascending')
                 ->with($this->equalTo($dynamicOperand));

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand);
    }

    /**
     * @return ConstraintInterface
     */
    private function createConstraintMock()
    {
        /** @var ConstraintInterface $constraint */
        $constraint = $this->getMockBuilder(ConstraintInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();

        return $constraint;
    }

    public function testWhere()
    {
        $constraint = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->where($constraint);
        $this->assertEquals($constraint, $qb->getConstraint());
    }

    public function testAndWhere()
    {
        $this->qf->expects($this->once())
                 ->method('andConstraint');

        $constraint1 = $this->createConstraintMock();
        $constraint2 = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->where($constraint1);
        $qb->andWhere($constraint2);
    }

    public function testOrWhere()
    {
        $this->qf->expects($this->once())
                 ->method('orConstraint');

        $constraint1 = $this->createConstraintMock();
        $constraint2 = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->where($constraint1);
        $qb->orWhere($constraint2);
    }

    public function testSelect()
    {
        $qb = new QueryBuilder($this->qf);
        $this->assertCount(0, $qb->getColumns());
        $qb->select('selectorName', 'propertyName', 'columnName');
        $this->assertCount(1, $qb->getColumns());
        $qb->select('selectorName', 'propertyName', 'columnName');
        $this->assertCount(1, $qb->getColumns());
    }

    public function testAddSelect()
    {
        $qb = new QueryBuilder($this->qf);
        $this->assertCount(0, $qb->getColumns());
        $qb->addSelect('selectorName', 'propertyName', 'columnName');
        $this->assertCount(1, $qb->getColumns());
        $qb->addSelect('selectorName', 'propertyName', 'columnName');
        $this->assertCount(2, $qb->getColumns());
    }

    /**
     * @return SourceInterface
     */
    private function createSourceMock()
    {
        /** @var SourceInterface $source */
        $source = $this->getMockBuilder(SourceInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();

        return $source;
    }

    public function testFrom()
    {
        $source = $this->createSourceMock();

        $qb = new QueryBuilder($this->qf);
        $qb->from($source);
        $this->assertEquals($source, $qb->getSource());
    }

    /**
     * @return SameNodeJoinConditionInterface
     */
    private function createSameNodeJoinConditionMock()
    {
        /** @var SameNodeJoinConditionInterface $joinCondition */
        $joinCondition = $this->getMockBuilder(SameNodeJoinConditionInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();

        return $joinCondition;
    }

    public function testInvalidJoin()
    {
        $this->expectException(RuntimeException::class);

        $source = $this->createSourceMock();
        $joinCondition = $this->createSameNodeJoinConditionMock();

        $qb = new QueryBuilder($this->qf);
        $qb->join($source, $joinCondition);
    }

    public function testJoin()
    {
        $source1 = $this->createSourceMock();
        $source2 = $this->createSourceMock();
        $joinCondition = $this->createSameNodeJoinConditionMock();

        $this->qf->expects($this->once())
            ->method('join')
            ->with(
                $source1,
                $source2,
                $this->equalTo(QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_INNER),
                $joinCondition
            );

        $qb = new QueryBuilder($this->qf);
        $qb->from($source1);
        $qb->join($source2, $joinCondition);
    }

    public function testRightJoin()
    {
        $source1 = $this->createSourceMock();
        $source2 = $this->createSourceMock();
        $joinCondition = $this->createSameNodeJoinConditionMock();

        $this->qf->expects($this->once())
                 ->method('join')
                 ->with(
                     $source1,
                     $source2,
                     $this->equalTo(QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_RIGHT_OUTER),
                     $joinCondition
                 );

        $qb = new QueryBuilder($this->qf);
        $qb->from($source1);
        $qb->rightJoin($source2, $joinCondition);
    }

    public function testLeftJoin()
    {
        $source1 = $this->createSourceMock();
        $source2 = $this->createSourceMock();
        $joinCondition = $this->createSameNodeJoinConditionMock();

        $this->qf->expects($this->once())
            ->method('join')
            ->with(
                $source1,
                $source2,
                $this->equalTo(QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_LEFT_OUTER),
                $joinCondition
            );

        $qb = new QueryBuilder($this->qf);
        $qb->from($source1);
        $qb->leftJoin($source2, $joinCondition);
    }

    public function testGetQuery()
    {
        $source = $this->createSourceMock();
        $constraint = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->from($source);
        $qb->where($constraint);

        $this->qf->expects($this->once())
                 ->method('createQuery')
                 ->will($this->returnValue('true'));

        $qb->getQuery();
    }

    /**
     * @return QueryObjectModelInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private function createQueryMock()
    {
        /** @var QueryObjectModelInterface $query */
        $query = $this->getMockBuilder(QueryObjectModelInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();

        return $query;
    }

    public function testGetQueryWithOffsetAndLimit()
    {
        $source = $this->createSourceMock();
        $constraint = $this->createConstraintMock();

        $query = $this->createQueryMock();

        $qb = new QueryBuilder($this->qf);
        $qb->from($source);
        $qb->where($constraint);
        $qb->setFirstResult(13);
        $qb->setMaxResults(42);

        $query->expects($this->once())
              ->method('setOffset');
        $query->expects($this->once())
              ->method('setLimit');

        $this->qf->expects($this->once())
                 ->method('createQuery')
                 ->will($this->returnValue($query));

        $qb->getQuery();
    }

    public function testSetParameter()
    {
        $key = 'key';
        $value = 'value';

        $qb = new QueryBuilder($this->qf);
        $qb->setParameter($key, $value);

        $this->assertEquals($value, $qb->getParameter($key));
    }

    public function testSetParameters()
    {
        $key1 = 'key1';
        $value1 = 'value1';
        $key2 = 'key2';
        $value2 = 'value2';

        $qb = new QueryBuilder($this->qf);
        $qb->setParameters([$key1, $value1], [$key2, $value2]);
        $this->assertCount(2, $qb->getParameters());
    }

    public function testExecute()
    {
        $source = $this->createSourceMock();
        $constraint = $this->createConstraintMock();
        $query = $this->createQueryMock();

        $query->expects($this->once())
              ->method('execute');
        $query->expects($this->once())
              ->method('bindValue');

        $this->qf->expects($this->once())
                 ->method('createQuery')
                 ->with($source, $constraint, [], [])
                 ->will($this->returnValue($query));

        $qb = new QueryBuilder($this->qf);
        $qb->from($source)
           ->where($constraint)
           ->setFirstResult(10)
           ->setMaxResults(10)
           ->setParameter('Key', 'value')
           ->execute();
    }
}

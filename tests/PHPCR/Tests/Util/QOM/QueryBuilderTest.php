<?php

declare(strict_types=1);

namespace PHPCR\Tests\Util\QOM;

use PHPCR\Query\QOM\ConstraintInterface;
use PHPCR\Query\QOM\DynamicOperandInterface;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Query\QOM\QueryObjectModelInterface;
use PHPCR\Query\QOM\SameNodeJoinConditionInterface;
use PHPCR\Query\QOM\SourceInterface;
use PHPCR\Query\QueryResultInterface;
use PHPCR\Util\QOM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryObjectModelFactoryInterface&MockObject
     */
    protected $qf;

    public function setUp(): void
    {
        $this->qf = $this->getMockBuilder(QueryObjectModelFactoryInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();
    }

    public function testSetFirstResult(): void
    {
        $qb = new QueryBuilder($this->qf);
        $qb->setFirstResult(15);
        $this->assertEquals(15, $qb->getFirstResult());
    }

    public function testSetMaxResults(): void
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

    public function testAddOrderBy(): void
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'ASC');
        $qb->addOrderBy($dynamicOperand, 'DESC');
        $this->assertCount(2, $qb->getOrderings());
        $orderings = $qb->getOrderings();
    }

    public function testAddOrderByLowercase(): void
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'asc');
        $qb->addOrderBy($dynamicOperand, 'desc');
        $this->assertCount(2, $qb->getOrderings());
        $orderings = $qb->getOrderings();
    }

    public function testAddOrderByInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'FOO');
    }

    public function testOrderBy(): void
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $qb = new QueryBuilder($this->qf);
        $qb->orderBy($dynamicOperand, 'ASC');
        $qb->orderBy($dynamicOperand, 'ASC');
        $this->assertCount(1, $qb->getOrderings());
    }

    public function testOrderAscending(): void
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $this->qf->expects($this->once())
                 ->method('ascending')
                 ->with($this->equalTo($dynamicOperand));

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'ASC');
    }

    public function testOrderDescending(): void
    {
        $dynamicOperand = $this->createDynamicOperandMock();

        $this->qf->expects($this->once())
                 ->method('descending')
                 ->with($this->equalTo($dynamicOperand));

        $qb = new QueryBuilder($this->qf);
        $qb->addOrderBy($dynamicOperand, 'DESC');
    }

    public function testOrderAscendingIsDefault(): void
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

    public function testWhere(): void
    {
        $constraint = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->where($constraint);
        $this->assertEquals($constraint, $qb->getConstraint());
    }

    public function testAndWhere(): void
    {
        $this->qf->expects($this->once())
                 ->method('andConstraint');

        $constraint1 = $this->createConstraintMock();
        $constraint2 = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->where($constraint1);
        $qb->andWhere($constraint2);
    }

    public function testOrWhere(): void
    {
        $this->qf->expects($this->once())
                 ->method('orConstraint');

        $constraint1 = $this->createConstraintMock();
        $constraint2 = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->where($constraint1);
        $qb->orWhere($constraint2);
    }

    public function testSelect(): void
    {
        $qb = new QueryBuilder($this->qf);
        $this->assertCount(0, $qb->getColumns());
        $qb->select('selectorName', 'propertyName', 'columnName');
        $this->assertCount(1, $qb->getColumns());
        $qb->select('selectorName', 'propertyName', 'columnName');
        $this->assertCount(1, $qb->getColumns());
    }

    public function testAddSelect(): void
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

    public function testFrom(): void
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

    public function testInvalidJoin(): void
    {
        $this->expectException(\RuntimeException::class);

        $source = $this->createSourceMock();
        $joinCondition = $this->createSameNodeJoinConditionMock();

        $qb = new QueryBuilder($this->qf);
        $qb->join($source, $joinCondition);
    }

    public function testJoin(): void
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

    public function testRightJoin(): void
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

    public function testLeftJoin(): void
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

    public function testGetQuery(): void
    {
        $source = $this->createSourceMock();
        $constraint = $this->createConstraintMock();

        $qb = new QueryBuilder($this->qf);
        $qb->from($source);
        $qb->where($constraint);

        $this->qf->expects($this->once())
                 ->method('createQuery')
                 ->willReturn($this->createMock(QueryObjectModelInterface::class));

        $qb->getQuery();
    }

    /**
     * @return QueryObjectModelInterface&MockObject
     */
    private function createQueryMock()
    {
        $query = $this->getMockBuilder(QueryObjectModelInterface::class)
            ->setMethods([])
            ->setConstructorArgs([])
            ->getMock();

        return $query;
    }

    public function testGetQueryWithOffsetAndLimit(): void
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
                 ->willReturn($query);

        $qb->getQuery();
    }

    public function testSetParameter(): void
    {
        $key = 'key';
        $value = 'value';

        $qb = new QueryBuilder($this->qf);
        $qb->setParameter($key, $value);

        $this->assertEquals($value, $qb->getParameter($key));
    }

    public function testSetParameters(): void
    {
        $key1 = 'key1';
        $value1 = 'value1';
        $key2 = 'key2';
        $value2 = 'value2';

        $qb = new QueryBuilder($this->qf);
        $qb->setParameters([
            $key1 => $value1,
            $key2 => $value2,
        ]);
        $this->assertCount(2, $qb->getParameters());
    }

    public function testExecute(): void
    {
        $source = $this->createSourceMock();
        $constraint = $this->createConstraintMock();
        $query = $this->createQueryMock();

        $result = $this->createMock(QueryResultInterface::class);
        $query->expects($this->once())
              ->method('execute')
            ->willReturn($result)
        ;
        $query->expects($this->once())
              ->method('bindValue');

        $this->qf->expects($this->once())
                 ->method('createQuery')
                 ->with($source, $constraint, [], [])
                 ->willReturn($query);

        $qb = new QueryBuilder($this->qf);
        $this->assertSame(
            $result,
            $qb->from($source)
               ->where($constraint)
               ->setFirstResult(10)
               ->setMaxResults(10)
               ->setParameter('Key', 'value')
               ->execute()
        );
    }
}

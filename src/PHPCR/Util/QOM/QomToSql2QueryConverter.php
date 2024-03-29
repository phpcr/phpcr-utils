<?php

declare(strict_types=1);

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;

/**
 * Convert a QOM query into an SQL2 statement.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class QomToSql2QueryConverter extends BaseQomToSqlQueryConverter
{
    /**
     * Source ::= Selector | Join.
     *
     * @throws \InvalidArgumentException
     */
    protected function convertSource(QOM\SourceInterface $source): string
    {
        if ($source instanceof QOM\SelectorInterface) {
            return $this->convertSelector($source);
        }
        if ($source instanceof QOM\JoinInterface) {
            return $this->convertJoin($source);
        }

        throw new \InvalidArgumentException('Invalid Source');
    }

    /**
     * Join ::= left [JoinType] 'JOIN' right 'ON' JoinCondition
     *    // If JoinType is omitted INNER is assumed.
     * left ::= Source
     * right ::= Source.
     *
     * JoinType ::= Inner | LeftOuter | RightOuter
     * Inner ::= 'INNER'
     * LeftOuter ::= 'LEFT OUTER'
     * RightOuter ::= 'RIGHT OUTER'
     */
    protected function convertJoin(QOM\JoinInterface $join): string
    {
        if (!$this->generator instanceof Sql2Generator) {
            throw new NotSupportedOperandException('Only SQL2 supports join');
        }

        $left = $this->convertSource($join->getLeft());
        $right = $this->convertSource($join->getRight());
        $condition = $this->convertJoinCondition($join->getJoinCondition());

        return $this->generator->evalJoin($left, $right, $condition, $this->generator->evalJoinType($join->getJoinType()));
    }

    /**
     * JoinCondition ::= EquiJoinCondition |
     *             SameNodeJoinCondition |
     *             ChildNodeJoinCondition |
     *             DescendantNodeJoinCondition.
     *
     * @throws \InvalidArgumentException
     */
    protected function convertJoinCondition(QOM\JoinConditionInterface $condition): string
    {
        if ($condition instanceof QOM\EquiJoinConditionInterface) {
            return $this->convertEquiJoinCondition($condition);
        }
        if ($condition instanceof QOM\SameNodeJoinConditionInterface) {
            return $this->convertSameNodeJoinCondition($condition);
        }
        if ($condition instanceof QOM\ChildNodeJoinConditionInterface) {
            return $this->convertChildNodeJoinCondition($condition);
        }
        if ($condition instanceof QOM\DescendantNodeJoinConditionInterface) {
            return $this->convertDescendantNodeJoinCondition($condition);
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException('Invalid operand');
    }

    /**
     * EquiJoinCondition ::= selector1Name'.'property1Name '='
     *                       selector2Name'.'property2Name
     *   selector1Name ::= selectorName
     *   selector2Name ::= selectorName
     *   property1Name ::= propertyName
     *   property2Name ::= propertyName.
     */
    protected function convertEquiJoinCondition(QOM\EquiJoinConditionInterface $condition): string
    {
        if (!$this->generator instanceof Sql2Generator) {
            throw new NotSupportedOperandException('Only SQL2 supports equi join condition');
        }

        return $this->generator->evalEquiJoinCondition(
            $condition->getSelector1Name(),
            $condition->getProperty1Name(),
            $condition->getSelector2Name(),
            $condition->getProperty2Name()
        );
    }

    /**
     * SameNodeJoinCondition ::=
     *   'ISSAMENODE(' selector1Name ','
     *                  selector2Name
     *                  [',' selector2Path] ')'
     *   selector2Path ::= Path.
     */
    protected function convertSameNodeJoinCondition(QOM\SameNodeJoinConditionInterface $condition): string
    {
        if (!$this->generator instanceof Sql2Generator) {
            throw new NotSupportedOperandException('Only SQL2 supports same node join condition');
        }

        return $this->generator->evalSameNodeJoinCondition(
            $condition->getSelector1Name(),
            $condition->getSelector2Name(),
            null !== $condition->getSelector2Path() ? $this->convertPath($condition->getSelector2Path()) : null
        );
    }

    /**
     * ChildNodeJoinCondition ::=
     *   'ISCHILDNODE(' childSelectorName ','
     *                  parentSelectorName ')'
     *   childSelectorName ::= selectorName
     *   parentSelectorName ::= selectorName.
     */
    protected function convertChildNodeJoinCondition(QOM\ChildNodeJoinConditionInterface $condition): string
    {
        if (!$this->generator instanceof Sql2Generator) {
            throw new NotSupportedOperandException('Only SQL2 supports child node join condition');
        }

        return $this->generator->evalChildNodeJoinCondition(
            $condition->getChildSelectorName(),
            $condition->getParentSelectorName()
        );
    }

    /**
     * DescendantNodeJoinCondition ::=
     *   'ISDESCENDANTNODE(' descendantSelectorName ','
     *                       ancestorSelectorName ')'
     *   descendantSelectorName ::= selectorName
     *   ancestorSelectorName ::= selectorName.
     */
    protected function convertDescendantNodeJoinCondition(QOM\DescendantNodeJoinConditionInterface $condition): string
    {
        if (!$this->generator instanceof Sql2Generator) {
            throw new NotSupportedOperandException('Only SQL2 supports descendant node join condition');
        }

        return $this->generator->evalDescendantNodeJoinCondition(
            $condition->getDescendantSelectorName(),
            $condition->getAncestorSelectorName()
        );
    }

    /**
     * Constraint ::= And | Or | Not | Comparison |
     *          PropertyExistence | FullTextSearch |
     *          SameNode | ChildNode | DescendantNode.
     *
     * And ::= constraint1 'AND' constraint2
     * Or ::= constraint1 'OR' constraint2
     * Not ::= 'NOT' Constraint
     *
     * SameNode ::= 'ISSAMENODE(' [selectorName ','] Path ')'
     *        // If only one selector exists in this query, explicit
     *           specification of the selectorName is optional
     *
     * ChildNode ::= 'ISCHILDNODE(' [selectorName ','] Path ')'
     *        // If only one selector exists in this query, explicit
     *           specification of the selectorName is optional
     *
     * DescendantNode ::= 'ISDESCENDANTNODE(' [selectorName ','] Path ')'
     *        // If only one selector exists in this query, explicit
     *           specification of the selectorName is optional
     *
     * @throws \InvalidArgumentException
     */
    protected function convertConstraint(QOM\ConstraintInterface $constraint): string
    {
        if ($constraint instanceof QOM\AndInterface) {
            return $this->generator->evalAnd(
                $this->convertConstraint($constraint->getConstraint1()),
                $this->convertConstraint($constraint->getConstraint2())
            );
        }

        if ($constraint instanceof QOM\OrInterface) {
            return $this->generator->evalOr(
                $this->convertConstraint($constraint->getConstraint1()),
                $this->convertConstraint($constraint->getConstraint2())
            );
        }

        if ($constraint instanceof QOM\NotInterface) {
            return $this->generator->evalNot($this->convertConstraint($constraint->getConstraint()));
        }

        if ($constraint instanceof QOM\ComparisonInterface) {
            return $this->convertComparison($constraint);
        }

        if ($constraint instanceof QOM\PropertyExistenceInterface) {
            return $this->convertPropertyExistence($constraint);
        }
        if ($constraint instanceof QOM\FullTextSearchInterface) {
            return $this->convertFullTextSearch($constraint);
        }

        if ($constraint instanceof QOM\SameNodeInterface) {
            if (!$this->generator instanceof Sql2Generator) {
                throw new NotSupportedConstraintException('Only SQL2 supports same node constraint');
            }

            return $this->generator->evalSameNode(
                $this->convertPath($constraint->getPath()),
                $constraint->getSelectorName()
            );
        }

        if ($constraint instanceof QOM\ChildNodeInterface) {
            return $this->generator->evalChildNode(
                $this->convertPath($constraint->getParentPath()),
                $constraint->getSelectorName()
            );
        }

        if ($constraint instanceof QOM\DescendantNodeInterface) {
            return $this->generator->evalDescendantNode(
                $this->convertPath($constraint->getAncestorPath()),
                $constraint->getSelectorName()
            );
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException('Invalid operand: '.$constraint::class);
    }

    /**
     * DynamicOperand ::= PropertyValue | Length | NodeName |
     *              NodeLocalName | FullTextSearchScore |
     *              LowerCase | UpperCase.
     *
     * Length ::= 'LENGTH(' PropertyValue ')'
     * NodeName ::= 'NAME(' [selectorName] ')'              // If only one selector exists
     * NodeLocalName ::= 'LOCALNAME(' [selectorName] ')'    // If only one selector exists
     * FullTextSearchScore ::= 'SCORE(' [selectorName] ')'  // If only one selector exists
     * LowerCase ::= 'LOWER(' DynamicOperand ')'
     * UpperCase ::= 'UPPER(' DynamicOperand ')'
     *
     * @throws \InvalidArgumentException
     */
    protected function convertDynamicOperand(QOM\DynamicOperandInterface $operand): string
    {
        if ($operand instanceof QOM\PropertyValueInterface) {
            return $this->convertPropertyValue($operand);
        }

        if ($operand instanceof QOM\LengthInterface) {
            if (!$this->generator instanceof Sql2Generator) {
                throw new NotSupportedOperandException('Only SQL2 supports length operand');
            }

            return $this->generator->evalLength($this->convertPropertyValue($operand->getPropertyValue()));
        }

        if ($operand instanceof QOM\NodeNameInterface) {
            if (!$this->generator instanceof Sql2Generator) {
                throw new NotSupportedOperandException('Only SQL2 supports node operand');
            }

            return $this->generator->evalNodeName($operand->getSelectorName());
        }

        if ($operand instanceof QOM\NodeLocalNameInterface) {
            if (!$this->generator instanceof Sql2Generator) {
                throw new NotSupportedOperandException('Only SQL2 supports local node name operand');
            }

            return $this->generator->evalNodeLocalName($operand->getSelectorName());
        }

        if ($operand instanceof QOM\FullTextSearchScoreInterface) {
            if (!$this->generator instanceof Sql2Generator) {
                throw new NotSupportedOperandException('Only SQL2 supports fulltext search score operand');
            }

            return $this->generator->evalFullTextSearchScore($operand->getSelectorName());
        }

        if ($operand instanceof QOM\LowerCaseInterface) {
            $operandName = $this->convertDynamicOperand($operand->getOperand());

            return $this->generator->evalLower($operandName);
        }

        if ($operand instanceof QOM\UpperCaseInterface) {
            $operandName = $this->convertDynamicOperand($operand->getOperand());

            return $this->generator->evalUpper($operandName);
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException('Invalid operand');
    }
}

<?php

/**
 * This file is part of the PHPCR Utils
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;

/**
 * Convert a QOM query into an SQL2 statement
 */
class QomToSql2QueryConverter extends BaseQomToSqlQueryConverter
{
    /**
     * Source ::= Selector | Join
     *
     * @param QOM\SourceInterface $source
     *
     * @return string
     */
    protected function convertSource(QOM\SourceInterface $source)
    {
        if ($source instanceof QOM\SelectorInterface) {
            return $this->convertSelector($source);
        }
        if ($source instanceof QOM\JoinInterface) {
            return $this->convertJoin($source);
        }

        throw new \InvalidArgumentException("Invalid Source");
    }

    /**
     * Join ::= left [JoinType] 'JOIN' right 'ON' JoinCondition
     *    // If JoinType is omitted INNER is assumed.
     * left ::= Source
     * right ::= Source
     *
     * JoinType ::= Inner | LeftOuter | RightOuter
     * Inner ::= 'INNER'
     * LeftOuter ::= 'LEFT OUTER'
     * RightOuter ::= 'RIGHT OUTER'
     *
     * @param  QOM\JoinInterface $join
     * @return string
     */
    protected function convertJoin(QOM\JoinInterface $join)
    {
        $left = $this->convertSource($join->getLeft());
        $right = $this->convertSource($join->getRight());
        $condition = $this->convertJoinCondition($join->getJoinCondition());

        return $this->generator->evalJoin($left, $right, $condition, $this->generator->evalJoinType($join->getJoinType()));
    }

    /**
     * JoinCondition ::= EquiJoinCondition |
     *             SameNodeJoinCondition |
     *             ChildNodeJoinCondition |
     *             DescendantNodeJoinCondition
     *
     * @param  QOM\JoinConditionInterface $condition
     * @return string
     */
    protected function convertJoinCondition(QOM\JoinConditionInterface $condition)
    {
        if ($condition instanceof QOM\EquiJoinConditionInterface) {
            $sql2 = $this->convertEquiJoinCondition($condition);
        } elseif ($condition instanceof QOM\SameNodeJoinConditionInterface) {
            $sql2 = $this->convertSameNodeJoinCondition($condition);
        } elseif ($condition instanceof QOM\ChildNodeJoinConditionInterface) {
            $sql2 = $this->convertChildNodeJoinCondition($condition);
        } elseif ($condition instanceof QOM\DescendantNodeJoinConditionInterface) {
            $sql2 = $this->convertDescendantNodeJoinCondition($condition);
        } else {
            // This should not happen, but who knows...
            throw new \InvalidArgumentException("Invalid operand");
        }

        return $sql2;
    }

    /**
     * EquiJoinCondition ::= selector1Name'.'property1Name '='
     *                       selector2Name'.'property2Name
     *   selector1Name ::= selectorName
     *   selector2Name ::= selectorName
     *   property1Name ::= propertyName
     *   property2Name ::= propertyName
     *
     * @param  QOM\EquiJoinConditionInterface $condition
     * @return string
     */
    protected function convertEquiJoinCondition(QOM\EquiJoinConditionInterface $condition)
    {
        return $this->generator->evalEquiJoinCondition(
            $condition->getSelector1Name(),
            $condition->getProperty1Name(),
            $condition->getSelector2Name(),
            $condition->getProperty2Name());
    }

    /**
     * SameNodeJoinCondition ::=
     *   'ISSAMENODE(' selector1Name ','
     *                  selector2Name
     *                  [',' selector2Path] ')'
     *   selector2Path ::= Path
     *
     * @param  QOM\SameNodeJoinConditionInterface $condition
     * @return string
     */
    protected function convertSameNodeJoinCondition(QOM\SameNodeJoinConditionInterface $condition)
    {
        return $this->generator->evalSameNodeJoinCondition(
            $condition->getSelector1Name(),
            $condition->getSelector2Name(),
            ! is_null($condition->getSelector2Path()) ? $this->convertPath($condition->getSelector2Path()) : null);
    }

    /**
     * ChildNodeJoinCondition ::=
     *   'ISCHILDNODE(' childSelectorName ','
     *                  parentSelectorName ')'
     *   childSelectorName ::= selectorName
     *   parentSelectorName ::= selectorName
     *
     * @param  QOM\ChildNodeJoinConditionInterface $condition
     * @return string
     */
    protected function convertChildNodeJoinCondition(QOM\ChildNodeJoinConditionInterface $condition)
    {
        return $this->generator->evalChildNodeJoinCondition(
            $condition->getChildSelectorName(),
            $condition->getParentSelectorName());
    }

    /**
     * DescendantNodeJoinCondition ::=
     *   'ISDESCENDANTNODE(' descendantSelectorName ','
     *                       ancestorSelectorName ')'
     *   descendantSelectorName ::= selectorName
     *   ancestorSelectorName ::= selectorName
     *
     * @param  QOM\DescendantNodeJoinConditionInterface $condition
     * @return string
     */
    protected function convertDescendantNodeJoinCondition(QOM\DescendantNodeJoinConditionInterface $condition)
    {
        return $this->generator->evalDescendantNodeJoinCondition(
            $condition->getDescendantSelectorName(),
            $condition->getAncestorSelectorName());
    }

    /**
     * Constraint ::= And | Or | Not | Comparison |
     *          PropertyExistence | FullTextSearch |
     *          SameNode | ChildNode | DescendantNode
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
     * @param  QOM\ConstraintInterface $constraint
     * @return string
     */
    protected function convertConstraint(QOM\ConstraintInterface $constraint)
    {
        if ($constraint instanceof QOM\AndInterface) {
            return $this->generator->evalAnd(
                $this->convertConstraint($constraint->getConstraint1()),
                $this->convertConstraint($constraint->getConstraint2()));
        }
        if ($constraint instanceof QOM\OrInterface) {
            return $this->generator->evalOr(
                $this->convertConstraint($constraint->getConstraint1()),
                $this->convertConstraint($constraint->getConstraint2()));
        }
        if ($constraint instanceof QOM\NotInterface) {
            return $this->generator->evalNot($this->convertConstraint($constraint->getConstraint()));
        }
        if ($constraint instanceof QOM\ComparisonInterface) {
            return $this->convertComparison($constraint);
        }
        if ($constraint instanceof QOM\PropertyExistenceInterface) {
            return $this->convertPropertyExistence($constraint);
        } elseif ($constraint instanceof QOM\FullTextSearchInterface) {
            return $this->convertFullTextSearch($constraint);
        }
        if ($constraint instanceof QOM\SameNodeInterface) {
            return $this->generator->evalSameNode(
                $this->convertPath($constraint->getPath()),
                $constraint->getSelectorName());
        }
        if ($constraint instanceof QOM\ChildNodeInterface) {
            return $this->generator->evalChildNode(
                $this->convertPath($constraint->getParentPath()),
                $constraint->getSelectorName());
        }
        if ($constraint instanceof QOM\DescendantNodeInterface) {
            return $this->generator->evalDescendantNode(
                $this->convertPath($constraint->getAncestorPath()),
                $constraint->getSelectorName());
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException("Invalid operand: " . get_class($constraint));
    }

    /**
     * DynamicOperand ::= PropertyValue | Length | NodeName |
     *              NodeLocalName | FullTextSearchScore |
     *              LowerCase | UpperCase
     *
     * Length ::= 'LENGTH(' PropertyValue ')'
     * NodeName ::= 'NAME(' [selectorName] ')'              // If only one selector exists
     * NodeLocalName ::= 'LOCALNAME(' [selectorName] ')'    // If only one selector exists
     * FullTextSearchScore ::= 'SCORE(' [selectorName] ')'  // If only one selector exists
     * LowerCase ::= 'LOWER(' DynamicOperand ')'
     * UpperCase ::= 'UPPER(' DynamicOperand ')'
     *
     * @param  QOM\DynamicOperandInterface $operand
     * @return string
     */
    protected function convertDynamicOperand(QOM\DynamicOperandInterface $operand)
    {
        if ($operand instanceof QOM\PropertyValueInterface) {
            return $this->convertPropertyValue($operand);
        }
        if ($operand instanceof QOM\LengthInterface) {
            return $this->generator->evalLength($this->convertPropertyValue($operand->getPropertyValue()));
        }

        if ($operand instanceof QOM\NodeNameInterface) {
            return $this->generator->evalNodeName($operand->getSelectorName());
        }

        if ($operand instanceof QOM\NodeLocalNameInterface) {
            return $this->generator->evalNodeLocalName($operand->getSelectorName());
        }
        if ($operand instanceof QOM\FullTextSearchScoreInterface) {
            return $this->generator->evalFullTextSearchScore($operand->getSelectorName());
        }
        if ($operand instanceof QOM\LowerCaseInterface) {
            $operand = $this->convertDynamicOperand($operand->getOperand());

            return $this->generator->evalLower($operand);
        }
        if ($operand instanceof QOM\UpperCaseInterface) {
            $operand = $this->convertDynamicOperand($operand->getOperand());

            return $this->generator->evalUpper($operand);
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException("Invalid operand");
    }

}

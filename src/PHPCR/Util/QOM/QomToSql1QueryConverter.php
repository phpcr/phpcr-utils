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
class QomToSql1QueryConverter extends BaseQomToSqlQueryConverter
{
    /**
     * Source ::= Selector
     *
     * @param  QOM\SourceInterface $source
     * @return string
     */
    protected function convertSource(QOM\SourceInterface $source)
    {
        if ($source instanceof QOM\SelectorInterface) {
            return $this->convertSelector($source);
        }

        throw new \InvalidArgumentException("Invalid Source");
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
            throw new NotSupportedConstraintException($constraint);
        }
        if ($constraint instanceof QOM\ChildNodeInterface) {
            return $this->generator->evalChildNode(
                $this->convertPath($constraint->getParentPath()));
        }
        if ($constraint instanceof QOM\DescendantNodeInterface) {
            return $this->generator->evalDescendantNode(
                $this->convertPath($constraint->getAncestorPath()));
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException("Invalid operand: " . get_class($constraint));
    }

    /**
     * DynamicOperand ::= PropertyValue | LowerCase | UpperCase
     *
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
            throw new NotSupportedOperandException($operand);
        }
        if ($operand instanceof QOM\NodeNameInterface) {
            throw new NotSupportedOperandException($operand);
        }
        if ($operand instanceof QOM\NodeLocalNameInterface) {
            throw new NotSupportedOperandException($operand);
        }
        if ($operand instanceof QOM\FullTextSearchScoreInterface) {
            throw new NotSupportedOperandException($operand);
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

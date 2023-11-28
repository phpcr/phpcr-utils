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
class QomToSql1QueryConverter extends BaseQomToSqlQueryConverter
{
    /**
     * Source ::= Selector.
     *
     * @throws \InvalidArgumentException
     */
    protected function convertSource(QOM\SourceInterface $source): string
    {
        if ($source instanceof QOM\SelectorInterface) {
            return $this->convertSelector($source);
        }

        throw new \InvalidArgumentException('Invalid Source');
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
     * @throws \InvalidArgumentException
     * @throws NotSupportedConstraintException
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
            throw NotSupportedConstraintException::fromConstraint($constraint);
        }

        if ($constraint instanceof QOM\ChildNodeInterface) {
            return $this->generator->evalChildNode(
                $this->convertPath($constraint->getParentPath())
            );
        }

        if ($constraint instanceof QOM\DescendantNodeInterface) {
            return $this->generator->evalDescendantNode(
                $this->convertPath($constraint->getAncestorPath())
            );
        }

        // This should not happen, but who knows...
        $class = $constraint::class;

        throw new \InvalidArgumentException("Invalid operand: $class");
    }

    /**
     * DynamicOperand ::= PropertyValue | LowerCase | UpperCase.
     *
     * LowerCase ::= 'LOWER(' DynamicOperand ')'
     * UpperCase ::= 'UPPER(' DynamicOperand ')'
     *
     * @throws NotSupportedOperandException
     * @throws \InvalidArgumentException
     */
    protected function convertDynamicOperand(QOM\DynamicOperandInterface $operand): string
    {
        if ($operand instanceof QOM\PropertyValueInterface) {
            return $this->convertPropertyValue($operand);
        }

        if ($operand instanceof QOM\LengthInterface) {
            throw NotSupportedOperandException::fromOperand($operand);
        }

        if ($operand instanceof QOM\NodeNameInterface) {
            throw NotSupportedOperandException::fromOperand($operand);
        }

        if ($operand instanceof QOM\NodeLocalNameInterface) {
            throw NotSupportedOperandException::fromOperand($operand);
        }

        if ($operand instanceof QOM\FullTextSearchScoreInterface) {
            throw NotSupportedOperandException::fromOperand($operand);
        }

        if ($operand instanceof QOM\LowerCaseInterface) {
            $operandText = $this->convertDynamicOperand($operand->getOperand());

            return $this->generator->evalLower($operandText);
        }

        if ($operand instanceof QOM\UpperCaseInterface) {
            $operandText = $this->convertDynamicOperand($operand->getOperand());

            return $this->generator->evalUpper($operandText);
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException('Invalid operand');
    }
}

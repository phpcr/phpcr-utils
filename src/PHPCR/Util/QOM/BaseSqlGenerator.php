<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

abstract class BaseSqlGenerator {

    /**
     * Query ::= 'SELECT' columns
     *     'FROM' Source
     *     ['WHERE' Constraint]
     *     ['ORDER BY' orderings]
     *
     * @param string $source
     * @param string $columns
     * @param string $constraint
     * @param string $ordering
     * @return string
     */
    public function evalQuery($source, $columns, $constraint = '', $orderings = '')
    {
        $sql1 = "SELECT $columns FROM $source";

        if ($constraint) {
            $sql1 .= " WHERE $constraint";
        }

        if ($orderings) {
            $sql1 .= " ORDER BY $orderings";
        }

        return $sql1;
    }

    /**
     * And ::= constraint1 'AND' constraint2
     *
     * @param string $constraint1
     * @param string $constraint2
     */
    public function evalAnd($constraint1, $constraint2)
    {
        return "$constraint1 AND $constraint2";
    }

    /**
     * Or ::= constraint1 'OR' constraint2
     *
     * @param string $constraint1
     * @param string $constraint2
     */
    public function evalOr($constraint1, $constraint2)
    {
        return "$constraint1 OR $constraint2";
    }

    /**
     * Not ::= 'NOT' Constraint
     *
     * @param string $constraint
     */
    public function evalNot($constraint)
    {
        return "NOT $constraint";
    }

    /**
     * Comparison ::= DynamicOperand Operator StaticOperand
     *
     * @param string $operand1
     * @param string $operator
     * @param string $operand2
     */
    public function evalComparison($operand1, $operator, $operand2)
    {
        return "$operand1 $operator $operand2";
    }

    /**
     * Operator ::= EqualTo | NotEqualTo | LessThan |
     *        LessThanOrEqualTo | GreaterThan |
     *        GreaterThanOrEqualTo | Like
     *
     * @param string $operator
     */
    public function evalOperator($operator)
    {
        switch ($operator) {
            case Constants::JCR_OPERATOR_EQUAL_TO:
                return '=';
            case Constants::JCR_OPERATOR_GREATER_THAN:
                return '>';
            case Constants::JCR_OPERATOR_GREATER_THAN_OR_EQUAL_TO:
                return '>=';
            case Constants::JCR_OPERATOR_LESS_THAN:
                return '<';
            case Constants::JCR_OPERATOR_LESS_THAN_OR_EQUAL_TO:
                return '<=';
            case Constants::JCR_OPERATOR_LIKE:
                return 'LIKE';
            case Constants::JCR_OPERATOR_NOT_EQUAL_TO:
                return '<>';
        }

        return '';
    }

    /**
     * LowerCase ::= 'LOWER(' DynamicOperand ')'
     *
     * @param string $operand
     */
    public function evalLower($operand)
    {
        return "LOWER($operand)";
    }

    /**
     * LowerCase ::= 'UPPER(' DynamicOperand ')'
     *
     * @param string $operand
     */
    public function evalUpper($operand)
    {
        return "UPPER($operand)";
    }

    public function evalOrderings($orderings)
    {
        $sql2 = '';

        foreach ($orderings as $ordering) {

            if ($sql2 !== '') {
                $sql2 .= ', ';
            }

            $sql2 .= $ordering;
        }
        return $sql2;
    }

    public function evalOrdering($operand, $order)
    {
        return "$operand $order";
    }

    public function evalOrder($order)
    {
        switch ($order) {
            case Constants::JCR_ORDER_ASCENDING:
                return 'ASC';
            case Constants::JCR_ORDER_DESCENDING:
                return 'DESC';
        }
        return '';
    }

    public function evalBindVariable($var)
    {
        return '$' . $var;
    }

    public function evalLiteral($literal)
    {
        if ($literal instanceof \DateTime) {
            $string = \PHPCR\PropertyType::convertType($literal, \PHPCR\PropertyType::STRING);
            return $this->evalCastLiteral($string, 'DATE');
        }
        if (is_bool($literal)) {
            $string = $literal ? 'true' : 'false';
            return $this->evalCastLiteral($string, 'BOOLEAN');
        }
        if (is_int($literal)) {
            $string = \PHPCR\PropertyType::convertType($literal, \PHPCR\PropertyType::STRING);
            return $this->evalCastLiteral($string, 'LONG');
        }
        if (is_float($literal)) {
            $string = \PHPCR\PropertyType::convertType($literal, \PHPCR\PropertyType::STRING);
            return $this->evalCastLiteral($string, 'DOUBLE');
        }

        return "'$literal'";
    }
}

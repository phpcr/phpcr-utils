<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;
use PHPCR\PropertyType;
use PHPCR\Util\ValueConverter;

/**
 * Common base class for SQL(1) and SQL2 generators.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */
abstract class BaseSqlGenerator
{
    /**
     * @var ValueConverter
     */
    protected $valueConverter;

    public function __construct(ValueConverter $valueConverter)
    {
        $this->valueConverter = $valueConverter;
    }

    /**
     * Query ::= 'SELECT' columns
     *     'FROM' Source
     *     ['WHERE' Constraint]
     *     ['ORDER BY' orderings]
     *
     * @param string $source
     * @param string $columns
     * @param string $constraint
     * @param string $orderings
     *
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
        return "($constraint1 AND $constraint2)";
    }

    /**
     * Or ::= constraint1 'OR' constraint2
     *
     * @param string $constraint1
     * @param string $constraint2
     */
    public function evalOr($constraint1, $constraint2)
    {
        return "($constraint1 OR $constraint2)";
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

        return $operator;
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

    /**
     * orderings ::= Ordering {',' Ordering}
     *
     * @param $orderings
     *
     * @return string
     */
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

    /**
     * Ordering ::= DynamicOperand [Order]
     *
     * @param $operand
     * @param $order
     *
     * @return string
     */
    public function evalOrdering($operand, $order)
    {
        return "$operand $order";
    }

    /**
     * Order ::= Ascending | Descending
     * Ascending ::= 'ASC'
     * Descending ::= 'DESC'
     *
     * @param $order
     *
     * @return string
     */
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

    /**
     * BindVariableValue ::= '$'bindVariableName
     * bindVariableName ::= Prefix
     *
     * @param $var
     *
     * @return string
     */
    public function evalBindVariable($var)
    {
        return '$' . $var;
    }

    /**
     * Literal ::= CastLiteral | UncastLiteral
     *
     * @param mixed $literal
     *
     * @return string
     */
    public function evalLiteral($literal)
    {
        if ($literal instanceof \DateTime) {
            $string = $this->valueConverter->convertType($literal, PropertyType::STRING);

            return $this->evalCastLiteral($string, 'DATE');
        }
        if (is_bool($literal)) {
            $string = $literal ? 'true' : 'false';

            return $this->evalCastLiteral($string, 'BOOLEAN');
        }
        if (is_int($literal)) {
            $string = $this->valueConverter->convertType($literal, PropertyType::STRING);

            return $this->evalCastLiteral($string, 'LONG');
        }
        if (is_float($literal)) {
            $string = $this->valueConverter->convertType($literal, PropertyType::STRING);

            return $this->evalCastLiteral($string, 'DOUBLE');
        }

        return "'$literal'";
    }

    /**
     * Cast a literal. This is different between SQL1 and SQL2.
     *
     * @param string $literal
     * @param string $type
     *
     * @return string
     */
    abstract public function evalCastLiteral($literal, $type);

    /**
     * Evaluate a path. This is different between SQL1 and SQL2.
     *
     * @param string $path
     *
     * @return string
     */
    abstract public function evalPath($path);

}

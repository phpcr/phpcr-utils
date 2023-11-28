<?php

namespace PHPCR\Util\QOM;

use PHPCR\PropertyType;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;
use PHPCR\Util\ValueConverter;

/**
 * Common base class for SQL(1) and SQL2 generators.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
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
     *     ['ORDER BY' orderings].
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
        $sql = "SELECT $columns FROM $source";

        if ($constraint) {
            $sql .= " WHERE $constraint";
        }

        if ($orderings) {
            $sql .= " ORDER BY $orderings";
        }

        return $sql;
    }

    /**
     * And ::= constraint1 'AND' constraint2.
     *
     * @param string $constraint1
     * @param string $constraint2
     *
     * @return string
     */
    public function evalAnd($constraint1, $constraint2)
    {
        return "($constraint1 AND $constraint2)";
    }

    /**
     * Or ::= constraint1 'OR' constraint2.
     *
     * @param string $constraint1
     * @param string $constraint2
     *
     * @return string
     */
    public function evalOr($constraint1, $constraint2)
    {
        return "($constraint1 OR $constraint2)";
    }

    /**
     * Not ::= 'NOT' Constraint.
     *
     * @param string $constraint
     *
     * @return string
     */
    public function evalNot($constraint)
    {
        return "(NOT $constraint)";
    }

    /**
     * Comparison ::= DynamicOperand Operator StaticOperand.
     *
     * @param string $operand1
     * @param string $operator
     * @param string $operand2
     *
     * @return string
     */
    public function evalComparison($operand1, $operator, $operand2)
    {
        return "$operand1 $operator $operand2";
    }

    /**
     * Operator ::= EqualTo | NotEqualTo | LessThan |
     *        LessThanOrEqualTo | GreaterThan |
     *        GreaterThanOrEqualTo | Like.
     *
     * @param string $operator
     *
     * @return string
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
     * LowerCase ::= 'LOWER(' DynamicOperand ')'.
     *
     * @param string $operand
     *
     * @return string
     */
    public function evalLower($operand)
    {
        return "LOWER($operand)";
    }

    /**
     * LowerCase ::= 'UPPER(' DynamicOperand ')'.
     *
     * @param string $operand
     *
     * @return string
     */
    public function evalUpper($operand)
    {
        return "UPPER($operand)";
    }

    /**
     * orderings ::= Ordering {',' Ordering}.
     *
     * @return string
     */
    public function evalOrderings($orderings)
    {
        $sql = '';

        foreach ($orderings as $ordering) {
            if ('' !== $sql) {
                $sql .= ', ';
            }

            $sql .= $ordering;
        }

        return $sql;
    }

    /**
     * Ordering ::= DynamicOperand [Order].
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
     * Descending ::= 'DESC'.
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
     * bindVariableName ::= Prefix.
     *
     * @return string
     */
    public function evalBindVariable($var)
    {
        return '$'.$var;
    }

    /**
     * Escape the illegal characters for inclusion in a fulltext statement. Escape Character is \\.
     *
     * @param string $string
     *
     * @return string Escaped String
     *
     * @see http://jackrabbit.apache.org/api/1.4/org/apache/jackrabbit/util/Text.html #escapeIllegalJcrChars
     */
    public function evalFullText($string)
    {
        $illegalCharacters = [
            '!' => '\\!', '(' => '\\(', ':' => '\\:', '^' => '\\^',
            '[' => '\\[', ']' => '\\]', '{' => '\\{', '}' => '\\}',
            '\"' => '\\\"', '?' => '\\?', "'" => "''",
        ];

        return strtr($string, $illegalCharacters);
    }

    /**
     * Literal ::= CastLiteral | UncastLiteral.
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
     * @param string      $nodeTypeName The node type of the selector. If it does not contain starting and ending
     *                                  brackets ([]), they will be added automatically.
     * @param string|null $selectorName The selector name. If it is different than the nodeTypeName, the alias is
     *                                  declared if supported by the SQL dialect.
     *
     * @return string
     */
    abstract public function evalSelector($nodeTypeName, $selectorName = null);

    /**
     * Evaluate a path. This is different between SQL1 and SQL2.
     *
     * @param string $path
     *
     * @return string|null
     */
    abstract public function evalPath($path);

    /**
     * columns ::= (Column ',' {Column}) | '*'.
     *
     * With empty columns, SQL1 is different from SQL2
     *
     * @return string
     */
    abstract public function evalColumns($columns);

    /**
     * @param string $selectorName
     * @param string $propertyName
     * @param string $colname
     *
     * @return string
     */
    abstract public function evalColumn($selectorName, $propertyName = null, $colname = null);

    /**
     * @return string
     */
    abstract public function evalPropertyExistence($selectorName, $propertyName);

    /**
     * @param string $propertyName
     * @param string $selectorName
     *
     * @return string
     */
    abstract public function evalPropertyValue($propertyName, $selectorName = null);

    /**
     * @param string $path
     * @param string $selectorName
     *
     * @return string
     */
    abstract public function evalChildNode($path, $selectorName = null);

    /**
     * @param string $path
     * @param string $selectorName
     *
     * @return string
     */
    abstract public function evalDescendantNode($path, $selectorName = null);

    /**
     * @param string $selectorName
     * @param string $searchExpression
     * @param string $propertyName
     *
     * @return string
     */
    abstract public function evalFullTextSearch($selectorName, $searchExpression, $propertyName = null);
}

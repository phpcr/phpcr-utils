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
    protected ValueConverter $valueConverter;

    public function __construct(ValueConverter $valueConverter)
    {
        $this->valueConverter = $valueConverter;
    }

    /**
     * Query ::= 'SELECT' columns
     *     'FROM' Source
     *     ['WHERE' Constraint]
     *     ['ORDER BY' orderings].
     */
    public function evalQuery(string $source, string $columns, string $constraint = '', string $orderings = ''): string
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
     */
    public function evalAnd(string $constraint1, string $constraint2): string
    {
        return "($constraint1 AND $constraint2)";
    }

    /**
     * Or ::= constraint1 'OR' constraint2.
     */
    public function evalOr(string $constraint1, string $constraint2): string
    {
        return "($constraint1 OR $constraint2)";
    }

    /**
     * Not ::= 'NOT' Constraint.
     */
    public function evalNot(string $constraint): string
    {
        return "(NOT $constraint)";
    }

    /**
     * Comparison ::= DynamicOperand Operator StaticOperand.
     */
    public function evalComparison(string $operand1, string $operator, string $operand2): string
    {
        return "$operand1 $operator $operand2";
    }

    /**
     * Operator ::= EqualTo | NotEqualTo | LessThan |
     *        LessThanOrEqualTo | GreaterThan |
     *        GreaterThanOrEqualTo | Like.
     */
    public function evalOperator(string $operator): string
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
     */
    public function evalLower(string $operand): string
    {
        return "LOWER($operand)";
    }

    /**
     * LowerCase ::= 'UPPER(' DynamicOperand ')'.
     */
    public function evalUpper(string $operand): string
    {
        return "UPPER($operand)";
    }

    /**
     * orderings ::= Ordering {',' Ordering}.
     *
     * @param string[] $orderings
     */
    public function evalOrderings(array $orderings): string
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
     */
    public function evalOrdering(string $operand, string $order): string
    {
        return "$operand $order";
    }

    /**
     * Order ::= Ascending | Descending
     * Ascending ::= 'ASC'
     * Descending ::= 'DESC'.
     */
    public function evalOrder($order): string
    {
        return match ($order) {
            Constants::JCR_ORDER_ASCENDING => 'ASC',
            Constants::JCR_ORDER_DESCENDING => 'DESC',
            default => '',
        };
    }

    /**
     * BindVariableValue ::= '$'bindVariableName
     * bindVariableName ::= Prefix.
     */
    public function evalBindVariable(string $var): string
    {
        return '$'.$var;
    }

    /**
     * Escape the illegal characters for inclusion in a fulltext statement. Escape Character is \\.
     *
     * @return string Escaped String
     *
     * @see http://jackrabbit.apache.org/api/1.4/org/apache/jackrabbit/util/Text.html #escapeIllegalJcrChars
     */
    public function evalFullText(string $string): string
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
     */
    public function evalLiteral(mixed $literal): string
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
     */
    abstract public function evalCastLiteral(string $literal, string $type): string;

    /**
     * @param string      $nodeTypeName The node type of the selector. If it does not contain starting and ending
     *                                  brackets ([]), they will be added automatically.
     * @param string|null $selectorName The selector name. If it is different than the nodeTypeName, the alias is
     *                                  declared if supported by the SQL dialect.
     */
    abstract public function evalSelector(string $nodeTypeName, string $selectorName = null): string;

    /**
     * Evaluate a path. This is different between SQL1 and SQL2.
     */
    abstract public function evalPath(string $path): string;

    /**
     * columns ::= (Column ',' {Column}) | '*'.
     *
     * With empty columns, SQL1 is different from SQL2
     *
     * @param iterable<string> $columns
     */
    abstract public function evalColumns(iterable $columns): string;

    abstract public function evalColumn(string $selectorName, string $propertyName = null, string $colname = null): string;

    abstract public function evalPropertyExistence(?string $selectorName, string $propertyName): string;

    abstract public function evalPropertyValue(string $propertyName, string $selectorName = null);

    abstract public function evalChildNode(string $path, string $selectorName = null);

    abstract public function evalDescendantNode(string $path, string $selectorName = null): string;

    abstract public function evalFullTextSearch(string $selectorName, string $searchExpression, string $propertyName = null): string;
}

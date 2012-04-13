<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

/**
 * Generate SQL1 statements
 *
 * TODO: is eval... the best name for the functions here?
 */
class Sql1Generator
{
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
     * Selector ::= nodeTypeName ['AS' selectorName]
     * nodeTypeName ::= Name
     *
     * @param string $nodeTypeName The node type of the selector. If it does not contain starting and ending brackets ([]) they will be added automatically
     * @param string $selectorName
     * @return string
     */
    public function evalSelector($nodeTypeName, $selectorName = null)
    {
        $sql1 = $nodeTypeName;
        if (substr($sql1, 0, 1) !== '[' && substr($sql1, -1) !== ']') {
            $sql1 =  $sql1;
        }

        $name = $selectorName;
        if (! is_null($name)) {
            $sql1 .= ' AS ' . $name;
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

    public function evalPropertyExistence($selectorName, $propertyName)
    {
        return "$propertyName IS NOT NULL";
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' ([selectorName'.']propertyName |
     *                    selectorName'.*') ','
     *                    FullTextSearchExpression ')'
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''
     *
     * @param \PHPCR\Query\QOM\FullTextSearchInterface $constraint
     * @return string
     */
    public function evalFullTextSearch($selectorName, $searchExpression, $propertyName = null)
    {
        $sql1 = 'CONTAINS(';
        $sql1 .= is_null($propertyName) ? '*' : $propertyName;
        $sql1 .= ', ' . $searchExpression . ')';

        return $sql1;
    }

    /**
     * Length ::= 'LENGTH(' PropertyValue ')'
     *
     * @param string $propertyValue
     * @return string
     */
    public function evalLength($propertyValue)
    {
        return "LENGTH($propertyValue)";
    }

    /**
     * NodeName ::= 'NAME(' [selectorName] ')'
     *
     * @param string $selectorValue
     */
    public function evalNodeName($selectorValue = null)
    {
        $selectorValue = is_null($selectorValue) ? '' : $selectorValue;
        return "NAME($selectorValue)";
    }

    /**
     * NodeLocalName ::= 'LOCALNAME(' [selectorName] ')'
     *
     * @param string $selectorValue
     */
    public function evalNodeLocalName($selectorValue = null)
    {
        $selectorValue = is_null($selectorValue) ? '' : $selectorValue;
        return "LOCALNAME($selectorValue)";
    }

    /**
     * FullTextSearchScore ::= 'SCORE(' [selectorName] ')'
     *
     * @param string $selectorValue
     */
    public function evalFullTextSearchScore($selectorValue = null)
    {
        $selectorValue = is_null($selectorValue) ? '' : $selectorValue;
        return "SCORE($selectorValue)";
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
     * PropertyValue ::= [selectorName'.'] propertyName     // If only one selector exists
     *
     * @param string $propertyName
     * @param string $selectorName
     */
    public function evalPropertyValue($propertyName, $selectorName = null)
    {
        if (false !== strpos($selectorName, ':')) {
            $selectorName = "$selectorName";
        }
        $sql1 = ! is_null($selectorName) ? $selectorName . '.' : '';
        if (false !== strpos($propertyName, ':')) {
            $propertyName = "$propertyName";
        }
        $sql1 .= $propertyName;
        return $sql1;
    }

    public function evalOrderings($orderings)
    {
        $sql1 = '';

        foreach ($orderings as $ordering) {

            if ($sql1 !== '') {
                $sql1 .= ', ';
            }

            $sql1 .= $ordering;
        }
        return $sql1;
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

    public function evalColumns($columns)
    {
        if (count($columns) === 0) {
            return 's';
        }

        $sql1 = '';
        foreach ($columns as $column) {

            if ($sql1 !== '') {
                $sql1 .= ', ';
            }

            $sql1 .= $column;
        }

        return $sql1;
    }

    public function evalColumn($selector, $property = null, $colname = null)
    {
        $sql1 = '';
        if (! is_null($selector) && is_null($property) && is_null($colname)) {
            $sql1 .= $selector . '.*';
        } else {
            $sql1 .= ! is_null($selector) ? $selector . '.' : '';
            $sql1 .= $property;
            $sql1 .= ! is_null($colname) ? ' AS ' . $colname : '';
        }
        return $sql1;
    }

    /**
     * Path ::= '[' quotedPath ']' | '[' simplePath ']' | simplePath
     * quotedPath ::= A JCR Path that contains non-SQL-legal characters
     * simplePath ::= A JCR Name that contains only SQL-legal characters
     *
     * @param string $path
     * @return string
     */
    public function evalPath($path)
    {
        if ($path) {
            $sql1 = $path;
            if (substr($path, 0,1) !== '[' && substr($path, -1) !== ']') {
                $sql1 = '[' . $sql1 . ']';
            }
            return $sql1;
        }
        return null;
    }

    public function evalBindVariable($var)
    {
        return '$' . $var;
    }

    /**
     * @param string $literal
     * @param string $type
     */
    public function evalCastLiteral($literal, $type)
    {
        return "CAST('$literal' AS $type)";
    }

    public function evalLiteral($literal)
    {
        if ($literal instanceof \DateTime) {
            $string = \PHPCR\PropertyType::convertType($literal, \PHPCR\PropertyType::STRING);
            return $this->evalCastLiteral($string, 'DATE');
        }
        return "'$literal'";
    }
}

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
     * Selector ::= nodeTypeName
     * nodeTypeName ::= Name
     *
     * @param string $nodeTypeName The node type of the selector. If it does not contain starting and ending brackets ([]) they will be added automatically
     * @return string
     */
    public function evalSelector($nodeTypeName)
    {
        return $nodeTypeName;
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

    
    protected function getPathForDescendantQuery($path) {
        $path = trim($path,"'");
        $path = trim($path,"/");
        $sql1 = "/" . str_replace("/","[%]/",$path) ;
        $sql1 .= "[%]/%";
        return $sql1;
    }
        
    
    /**
     * SameNode ::= 'jcr:path like Path/% and not jcr:path like Path/%/%' 
     *
     * @param string $path
     * @param string $selectorName
     */
    public function evalChildNode($path, $selectorName = null)
    {
        $path = $this->getPathForDescendantQuery($path);
        $sql1 = "jcr:path LIKE '" . $path ."'";
        $sql1 .= " AND NOT jcr:path LIKE '" . $path . "/%'";
        return $sql1;    
    }
   
    /**
     * SameNode ::= 'jcr:path like Path/%'
     *
     * @param string $path
     * @param string $selectorName
     */
    public function evalDescendantNode($path)
    {
        $path = $this->getPathForDescendantQuery($path);
        $sql1 = "jcr:path LIKE '" . $path . "'";
        return $sql1;
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

    public function evalPropertyExistence($propertyName)
    {
        return "$propertyName IS NOT NULL";
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' (propertyName | '*') ') ','
     *                    FullTextSearchExpression ')'
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''
     *
     * @param \PHPCR\Query\QOM\FullTextSearchInterface $constraint
     * @return string
     */
    public function evalFullTextSearch($searchExpression, $propertyName = null)
    {
        $sql1 = 'CONTAINS(';
        $sql1 .= is_null($propertyName) ? '*' : $propertyName;
        $sql1 .= ', ' . $searchExpression . ')';

        return $sql1;
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
     * PropertyValue ::= propertyName
     *
     * @param string $propertyName
     */
    public function evalPropertyValue($propertyName)
    {
        return $propertyName;
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

    public function evalColumn($property = null)
    {
        return $property;
    }

    /**
     * Path ::= simplePath
     * simplePath ::= A JCR Name that contains only SQL-legal characters
     *
     * @param string $path
     * @return string
     */
    public function evalPath($path)
    {
        return $path;
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

<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

abstract class BaseQomToSqlQueryConverter {

    /**
     * @var \PHPCR\Util\QOM\Sql2Generator
     */
    protected $generator;

    public function __construct(BaseSqlGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Query ::= 'SELECT' columns
     *     'FROM' Source
     *     ['WHERE' Constraint]
     *     ['ORDER BY' orderings]
     *
     * @param \PHPCR\Query\QOM\QueryObjectModelInterface $query
     * @return string
     */
    public function convert(QOM\QueryObjectModelInterface $query)
    {
        $columns = $this->convertColumns($query->getColumns());
        $source = $this->convertSource($query->getSource());
        $constraint = '';
        $orderings = '';

        if ($query->getConstraint() !== null) {
            $constraint = $this->convertConstraint($query->getConstraint());
        }

        if (count($query->getOrderings())) {
            $orderings = $this->convertOrderings($query->getOrderings());
        }

        return $this->generator->evalQuery($source, $columns, $constraint, $orderings);
    }

    /**
     * Selector ::= nodeTypeName ['AS' selectorName]
     * nodeTypeName ::= Name
     *
     * @param \PHPCR\Query\QOM\SelectorInterface $selector
     * @return string
     */
    protected function convertSelector(QOM\SelectorInterface $selector)
    {
        return $this->generator->evalSelector($selector->getNodeTypeName(), $selector->getSelectorName());
    }

    /**
     * Comparison ::= DynamicOperand Operator StaticOperand
     *
     * Operator ::= EqualTo | NotEqualTo | LessThan |
     *        LessThanOrEqualTo | GreaterThan |
     *        GreaterThanOrEqualTo | Like
     * EqualTo ::= '='
     * NotEqualTo ::= '<>'
     * LessThan ::= '<'
     * LessThanOrEqualTo ::= '<='
     * GreaterThan ::= '>'
     * GreaterThanOrEqualTo ::= '>='
     * Like ::= 'LIKE'
     *
     * @param \PHPCR\Query\QOM\ComparisonInterface $comparison
     * @return string
     */
    protected function convertComparison(QOM\ComparisonInterface $comparison)
    {
        $operand1 = $this->convertDynamicOperand($comparison->getOperand1());
        $operand2 = $this->convertStaticOperand($comparison->getOperand2());
        $operator = $this->generator->evalOperator($comparison->getOperator());

        return $this->generator->evalComparison($operand1, $operator, $operand2);
    }

    /**
     * PropertyExistence ::=
     *   selectorName'.'propertyName 'IS NOT NULL' |
     *   propertyName 'IS NOT NULL'    If only one
     *                                 selector exists in
     *                                 this query
     *
     *   Note: The negation, 'NOT x IS NOT NULL'
     *      can be written 'x IS NULL'
     *
     * @param \PHPCR\Query\QOM\PropertyExistenceInterface $constraint
     * @return string
     */
    protected function convertPropertyExistence(QOM\PropertyExistenceInterface $constraint)
    {
        return $this->generator->evalPropertyExistence(
            $constraint->getSelectorName(),
            $constraint->getPropertyName());
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' ([selectorName'.']propertyName |
     *                    selectorName'.*') ','
     *                    FullTextSearchExpression ')'
     *                      // If only one selector exists in this query,
     *                         explicit specification of the selectorName
     *                         preceding the propertyName is optional
     *
     * @param \PHPCR\Query\QOM\FullTextSearchInterface $constraint
     * @return string
     */
    protected function convertFullTextSearch(QOM\FullTextSearchInterface $constraint)
    {
        $searchExpression = $this->convertFullTextSearchExpression($constraint->getFullTextSearchExpression());
        return $this->generator->evalFullTextSearch($constraint->getSelectorName(), $searchExpression, $constraint->getPropertyName());
    }

    /**
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''
     *
     * @param string $expr
     * @return string
     */
    protected function convertFullTextSearchExpression($literal)
    {
        if ($literal instanceof QOM\BindVariableValue) {
            return $this->convertBindVariable($literal);
        }
        if ($literal instanceof QOM\Literal) {
            return $this->convertLiteral($literal);
        }

        return "'$literal'";
    }


    /**
     * StaticOperand ::= Literal | BindVariableValue
     *
     * Literal ::= CastLiteral | UncastLiteral
     * CastLiteral ::= 'CAST(' UncastLiteral ' AS ' PropertyType ')'
     *
     * PropertyType ::= 'STRING' | 'BINARY' | 'DATE' | 'LONG' | 'DOUBLE' |
     *                  'DECIMAL' | 'BOOLEAN' | 'NAME' | 'PATH' |
     *                  'REFERENCE' | 'WEAKREFERENCE' | 'URI'
     * UncastLiteral ::= UnquotedLiteral | ''' UnquotedLiteral ''' | '“' UnquotedLiteral '“'
     * UnquotedLiteral ::= // String form of a JCR Value
     *
     * BindVariableValue ::= '$'bindVariableName
     * bindVariableName ::= Prefix
     *
     * @param \PHPCR\Query\QOM\StaticOperandInterface $operand
     * @return string
     */
    protected function convertStaticOperand(QOM\StaticOperandInterface $operand)
    {
        if ($operand instanceof QOM\BindVariableValueInterface) {
            return $this->convertBindVariable($operand->getBindVariableName());
        }
        if ($operand instanceof QOM\LiteralInterface) {
            return $this->convertLiteral($operand->getLiteralValue());
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException("Invalid operand");
    }

    /**
     * PropertyValue ::= [selectorName'.'] propertyName     // If only one selector exists
     *
     * @param \PHPCR\Query\QOM\PropertyValueInterface $value
     * @return string
     */
    protected function convertPropertyValue(QOM\PropertyValueInterface $operand)
    {
        return $this->generator->evalPropertyValue(
            $operand->getPropertyName(),
            $operand->getSelectorName());
    }


    /**
     * orderings ::= Ordering {',' Ordering}
     * Ordering ::= DynamicOperand [Order]
     * Order ::= Ascending | Descending
     * Ascending ::= 'ASC'
     * Descending ::= 'DESC'
     *
     * @param array $orderings
     * @return string
     */
    protected function convertOrderings(array $orderings)
    {
        $list = array();
        foreach ($orderings as $ordering) {
            $order = $this->generator->evalOrder($ordering->getOrder());
            $operand = $this->convertDynamicOperand($ordering->getOperand());
            $list[] = $this->generator->evalOrdering($operand, $order);
        }

        return $this->generator->evalOrderings($list);
    }

    protected function convertPath($path)
    {
        return $this->generator->evalPath($path);
    }

    protected function convertBindVariable($var)
    {
        return $this->generator->evalBindVariable($var);
    }

    protected function convertLiteral($literal)
    {
        return $this->generator->evalLiteral($literal);
    }

    /**
     * columns ::= (Column ',' {Column}) | '*'
     * Column ::= ([selectorName'.']propertyName
     *             ['AS' columnName]) |
     *            (selectorName'.*')    // If only one selector exists
     * selectorName ::= Name
     * propertyName ::= Name
     * columnName ::= Name
     *
     * @param array $columns
     * @return string
     */
    protected function convertColumns(array $columns)
    {
        $list = array();
        foreach ($columns as $column) {
            $selector = $column->getSelectorName();
            $property = $column->getPropertyName();
            $colname = $column->getColumnName();
            $list[] = $this->generator->evalColumn($selector, $property, $colname);
        }

        return $this->generator->evalColumns($list);
    }

}

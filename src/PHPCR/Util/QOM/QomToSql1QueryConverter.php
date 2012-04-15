<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

/**
 * Convert a QOM query into an SQL2 statement
 */
class QomToSql1QueryConverter
{
    /**
     * @var \PHPCR\Util\QOM\Sql2Generator
     */
    protected $generator;

    public function __construct(Sql1Generator $generator)
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
     * Source ::= Selector | Join
     *
     * @param \PHPCR\Query\QOM\SourceInterface $source
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
     * Selector ::= nodeTypeName
     * nodeTypeName ::= Name
     *
     * @param \PHPCR\Query\QOM\SelectorInterface $selector
     * @return string
     */
    protected function convertSelector(QOM\SelectorInterface $selector)
    {
        return $this->generator->evalSelector($selector->getNodeTypeName());
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
     * @param \PHPCR\Query\QOM\ConstraintInterface $constraint
     * @return string
     */
    protected function convertConstraint(QOM\ConstraintInterface $constraint)
    {
        if ($constraint instanceof QOM\AndInterface)
        {
            return $this->generator->evalAnd(
                $this->convertConstraint($constraint->getConstraint1()),
                $this->convertConstraint($constraint->getConstraint2()));
        }
        elseif ($constraint instanceof QOM\OrInterface)
        {
            return $this->generator->evalOr(
                $this->convertConstraint($constraint->getConstraint1()),
                $this->convertConstraint($constraint->getConstraint2()));
        }
        elseif ($constraint instanceof QOM\NotInterface)
        {
            return $this->generator->evalNot($this->convertConstraint($constraint->getConstraint()));
        }
        elseif ($constraint instanceof QOM\ComparisonInterface)
        {
            return $this->convertComparison($constraint);
        }
        elseif ($constraint instanceof QOM\PropertyExistenceInterface)
        {
            return $this->convertPropertyExistence($constraint);
        }
        elseif ($constraint instanceof QOM\FullTextSearchInterface)
        {
            return $this->convertFullTextSearch($constraint);
        }
        elseif ($constraint instanceof QOM\SameNodeInterface)
        {
            throw new NotSupportedConstraintException($constraint);
        }
        elseif ($constraint instanceof QOM\ChildNodeInterface)
        {
            throw new NotSupportedConstraintException($constraint);
        }
        elseif ($constraint instanceof QOM\DescendantNodeInterface)
        {
            throw new NotSupportedConstraintException($constraint);
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException("Invalid operand: " . get_class($constraint));
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
     *   propertyName 'IS NOT NULL'
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
            $constraint->getPropertyName());
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' (propertyName |
     *                    '*') ','
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
        return $this->generator->evalFullTextSearch($searchExpression, $constraint->getPropertyName());
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
     * DynamicOperand ::= PropertyValue | Length | NodeName |
     *              NodeLocalName | FullTextSearchScore |
     *              LowerCase | UpperCase
     *
     * Length ::= 'LENGTH(' PropertyValue ')'
     * NodeName ::= 'NAME()'              // If only one selector exists
     * NodeLocalName ::= 'LOCALNAME()'    // If only one selector exists
     * FullTextSearchScore ::= 'SCORE()'  // If only one selector exists
     * LowerCase ::= 'LOWER(' DynamicOperand ')'
     * UpperCase ::= 'UPPER(' DynamicOperand ')'
     *
     * @param \PHPCR\Query\QOM\DynamicOperandInterface $operand
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
            return $this->generator->evalNodeName();
        }

        if ($operand instanceof QOM\NodeLocalNameInterface) {
            return $this->generator->evalNodeLocalName();
        }
        if ($operand instanceof QOM\FullTextSearchScoreInterface) {
            return $this->generator->evalFullTextSearchScore();
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

    /**
     * PropertyValue ::=  propertyName     // If only one selector exists
     *
     * @param \PHPCR\Query\QOM\PropertyValueInterface $value
     * @return string
     */
    protected function convertPropertyValue(QOM\PropertyValueInterface $operand)
    {
        return $this->generator->evalPropertyValue(
            $operand->getPropertyName()
            );
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
        if ($operand instanceof QOM\BindVariableValueInterface)
        {
            return $this->convertBindVariable($operand->getBindVariableName());
        }
        elseif ($operand instanceof QOM\LiteralInterface)
        {
            return $this->convertLiteral($operand->getLiteralValue());
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException("Invalid operand");
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

    /**
     * columns ::= (Column ',' {Column}) | '*'
     * Column ::= (propertyName | '*')
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
            $property = $column->getPropertyName();
            $list[] = $this->generator->evalColumn($property);
        }
        return $this->generator->evalColumns($list);
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

}

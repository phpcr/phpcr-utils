<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\StaticOperandInterface;

/**
 * Common base class for the SQL(1) and SQL2 converters.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
abstract class BaseQomToSqlQueryConverter
{
    protected BaseSqlGenerator $generator;

    /**
     * Instantiate the converter.
     */
    public function __construct(BaseSqlGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Query ::= 'SELECT' columns
     *     'FROM' Source
     *     ['WHERE' Constraint]
     *     ['ORDER BY' orderings].
     */
    public function convert(QOM\QueryObjectModelInterface $query): string
    {
        $columns = $this->convertColumns($query->getColumns());
        $source = $this->convertSource($query->getSource());
        $constraint = '';
        $orderings = '';

        if (null !== $query->getConstraint()) {
            $constraint = $this->convertConstraint($query->getConstraint());
        }

        if (count($query->getOrderings())) {
            $orderings = $this->convertOrderings($query->getOrderings());
        }

        return $this->generator->evalQuery($source, $columns, $constraint, $orderings);
    }

    /**
     * Convert a source. This is different between SQL1 and SQL2.
     */
    abstract protected function convertSource(QOM\SourceInterface $source): string;

    /**
     * Convert a constraint. This is different between SQL1 and SQL2.
     */
    abstract protected function convertConstraint(QOM\ConstraintInterface $constraint): string;

    /**
     * Convert dynamic operand. This is different between SQL1 and SQL2.
     */
    abstract protected function convertDynamicOperand(QOM\DynamicOperandInterface $operand): mixed;

    /**
     * Selector ::= nodeTypeName ['AS' selectorName]
     * nodeTypeName ::= Name.
     */
    protected function convertSelector(QOM\SelectorInterface $selector): string
    {
        return $this->generator->evalSelector($selector->getNodeTypeName(), $selector->getSelectorName());
    }

    /**
     * Comparison ::= DynamicOperand Operator StaticOperand.
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
     */
    protected function convertComparison(QOM\ComparisonInterface $comparison): string
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
     *                                 this query.
     *
     *   Note: The negation, 'NOT x IS NOT NULL'
     *      can be written 'x IS NULL'
     */
    protected function convertPropertyExistence(QOM\PropertyExistenceInterface $constraint): string
    {
        return $this->generator->evalPropertyExistence(
            $constraint->getSelectorName(),
            $constraint->getPropertyName()
        );
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' ([selectorName'.']propertyName |
     *                    selectorName'.*') ','
     *                    FullTextSearchExpression ')'
     *                      // If only one selector exists in this query,
     *                         explicit specification of the selectorName
     *                         preceding the propertyName is optional.
     */
    protected function convertFullTextSearch(QOM\FullTextSearchInterface $constraint): string
    {
        $searchExpression = $this->convertFullTextSearchExpression($constraint->getFullTextSearchExpression());

        return $this->generator->evalFullTextSearch($constraint->getSelectorName(), $searchExpression, $constraint->getPropertyName());
    }

    /**
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''.
     *
     * @param string|QOM\StaticOperandInterface $expr
     */
    protected function convertFullTextSearchExpression($expr): string
    {
        if ($expr instanceof QOM\BindVariableValueInterface) {
            return $this->convertBindVariable($expr->getBindVariableName());
        }
        if ($expr instanceof QOM\LiteralInterface) {
            $literal = $expr->getLiteralValue();
        } elseif (is_string($expr)) {
            // this should not happen, the interface for full text search declares the return type to be StaticOperandInterface
            // however, without type checks, jackalope 1.0 got this wrong and returned a string.
            $literal = $expr;
        } else {
            throw new \InvalidArgumentException('Unknown full text search expression type '.get_class($expr));
        }

        $literal = $this->generator->evalFullText($literal);

        return "'$literal'";
    }

    /**
     * StaticOperand ::= Literal | BindVariableValue.
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
     * @throws \InvalidArgumentException
     */
    protected function convertStaticOperand(QOM\StaticOperandInterface $operand): string
    {
        if ($operand instanceof QOM\BindVariableValueInterface) {
            return $this->convertBindVariable($operand->getBindVariableName());
        }
        if ($operand instanceof QOM\LiteralInterface) {
            return $this->convertLiteral($operand->getLiteralValue());
        }

        // This should not happen, but who knows...
        throw new \InvalidArgumentException('Invalid operand');
    }

    /**
     * PropertyValue ::= [selectorName'.'] propertyName     // If only one selector exists.
     */
    protected function convertPropertyValue(QOM\PropertyValueInterface $value): string
    {
        return $this->generator->evalPropertyValue(
            $value->getPropertyName(),
            $value->getSelectorName()
        );
    }

    /**
     * orderings ::= Ordering {',' Ordering}
     * Ordering ::= DynamicOperand [Order]
     * Order ::= Ascending | Descending
     * Ascending ::= 'ASC'
     * Descending ::= 'DESC'.
     *
     * @param QOM\OrderingInterface[] $orderings
     */
    protected function convertOrderings(array $orderings): string
    {
        $list = [];
        foreach ($orderings as $ordering) {
            $order = $this->generator->evalOrder($ordering->getOrder());
            $operand = $this->convertDynamicOperand($ordering->getOperand());
            $list[] = $this->generator->evalOrdering($operand, $order);
        }

        return $this->generator->evalOrderings($list);
    }

    /**
     * Path ::= '[' quotedPath ']' | '[' simplePath ']' | simplePath
     * quotedPath ::= A JCR Path that contains non-SQL-legal characters
     * simplePath ::= A JCR Name that contains only SQL-legal characters.
     */
    protected function convertPath(string $path): string
    {
        return $this->generator->evalPath($path);
    }

    /**
     * BindVariableValue ::= '$'bindVariableName
     * bindVariableName ::= Prefix.
     */
    protected function convertBindVariable(string $var): string
    {
        return $this->generator->evalBindVariable($var);
    }

    /**
     * Literal ::= CastLiteral | UncastLiteral.
     */
    protected function convertLiteral(mixed $literal): string
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
     * columnName ::= Name.
     *
     * @param QOM\ColumnInterface[] $columns
     */
    protected function convertColumns(array $columns): string
    {
        $list = [];

        foreach ($columns as $column) {
            $selector = $column->getSelectorName();
            $property = $column->getPropertyName();
            $colname = $column->getColumnName();
            $list[] = $this->generator->evalColumn($selector, $property, $colname);
        }

        return $this->generator->evalColumns($list);
    }
}

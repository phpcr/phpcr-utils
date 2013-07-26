<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\InvalidQueryException;
use PHPCR\Query\QOM\ChildNodeJoinConditionInterface;
use PHPCR\Query\QOM\ColumnInterface;
use PHPCR\Query\QOM\ComparisonInterface;
use PHPCR\Query\QOM\ConstraintInterface;
use PHPCR\Query\QOM\DescendantNodeJoinConditionInterface;
use PHPCR\Query\QOM\DynamicOperandInterface;
use PHPCR\Query\QOM\EquiJoinConditionInterface;
use PHPCR\Query\QOM\FullTextSearchInterface;
use PHPCR\Query\QOM\JoinConditionInterface;
use PHPCR\Query\QOM\LiteralInterface;
use PHPCR\Query\QOM\NotInterface;
use PHPCR\Query\QOM\PropertyExistenceInterface;
use PHPCR\Query\QOM\PropertyValueInterface;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Query\QOM\QueryObjectModelInterface;
use PHPCR\Query\QOM\SameNodeJoinConditionInterface;
use PHPCR\Query\QOM\SourceInterface;
use PHPCR\Query\QOM\StaticOperandInterface;

/**
 * Parse SQL2 statements and output a corresponding QOM objects tree.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Sql2ToQomQueryConverter
{
    /**
     * The factory to create QOM objects
     *
     * @var QueryObjectModelFactoryInterface
     */
    protected $factory;

    /**
     * Scanner to parse SQL2
     *
     * @var Sql2Scanner;
     */
    protected $scanner;

    /**
     * The SQL2 query (the converter is not reentrant)
     *
     * @var string
     */
    protected $sql2;

    /**
     * The selector is not required for SQL2 but for QOM.
     *
     * We keep all selectors we encounter. If there is exactly one, it is used
     * whenever we encounter non-qualified names.
     *
     * @var string|array
     */
    protected $implicitSelectorName = null;

    /**
     * Instantiate a converter
     *
     * @param QueryObjectModelFactoryInterface $factory
     */
    public function __construct(QueryObjectModelFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * 6.7.1. Query
     * Parse an SQL2 query and return the corresponding QOM QueryObjectModel
     *
     * @param string $sql2
     *
     * @return QueryObjectModelInterface
     */
    public function parse($sql2)
    {
        $this->implicitSelectorName = null;
        $this->sql2 = $sql2;
        $this->scanner = new Sql2Scanner($sql2);
        $source = null;
        $columnData = array();
        $constraint = null;
        $orderings = array();

        while ($this->scanner->lookupNextToken() !== '') {
            switch (strtoupper($this->scanner->lookupNextToken())) {
                case 'SELECT':
                    $columnData = $this->scanColumns();
                    break;
                case 'FROM':
                    $source = $this->parseSource();
                    break;
                case 'WHERE':
                    $this->scanner->expectToken('WHERE');
                    $constraint = $this->parseConstraint();
                    break;
                case 'ORDER':
                    // Ordering, check there is a BY
                    $this->scanner->expectTokens(array('ORDER', 'BY'));
                    $orderings = $this->parseOrderings();
                    break;
                default:
                    throw new InvalidQueryException('Expected end of query, got ' . $this->scanner->lookupNextToken() . ' in ' . $this->sql2);
            }
        }

        if (!$source instanceof SourceInterface) {
            throw new InvalidQueryException('Invalid query, source could not be determined: '.$sql2);
        }

        $columns = $this->buildColumns($columnData);

        $query = $this->factory->createQuery($source, $constraint, $orderings, $columns);

        return $query;
    }

    /**
     * 6.7.2. Source
     * Parse an SQL2 source definition and return the corresponding QOM Source
     *
     * @return SourceInterface
     */
    protected function parseSource()
    {
        $this->scanner->expectToken('FROM');

        $selector = $this->parseSelector();

        $next = $this->scanner->lookupNextToken();
        if (in_array(strtoupper($next), array('JOIN', 'INNER', 'RIGHT', 'LEFT'))) {
            return $this->parseJoin($selector);
        }

        return $selector;
    }

    /**
     * 6.7.3. Selector
     * Parse an SQL2 selector and return a QOM\Selector
     *
     * @return \PHPCR\Query\QOM\SelectorInterface
     */
    protected function parseSelector()
    {
        $nodetype = $this->fetchTokenWithoutBrackets();

        if ($this->scanner->tokenIs($this->scanner->lookupNextToken(), 'AS')) {
            $this->scanner->fetchNextToken(); // Consume the AS
            $selectorName = $this->parseName();
            $this->updateImplicitSelectorName($selectorName);

            return $this->factory->selector($selectorName, $nodetype);
        }
        $this->updateImplicitSelectorName($nodetype);

        return $this->factory->selector($nodetype, $nodetype);
    }

    /**
     * 6.7.4. Name
     *
     * @return string
     */
    protected function parseName()
    {
        return $this->scanner->fetchNextToken();
    }

    /**
     * 6.7.5. Join
     * 6.7.6. Join type
     * Parse an SQL2 join source and return a QOM\Join
     *
     * @param string $leftSelector the left selector as it has been read by parseSource
     * return \PHPCR\Query\QOM\JoinInterface
     */
    protected function parseJoin($leftSelector)
    {
        $joinType = $this->parseJoinType();
        $right = $this->parseSelector();
        $joinCondition = $this->parseJoinCondition();

        return $this->factory->join($leftSelector, $right, $joinType, $joinCondition);
    }

    /**
     * 6.7.6. Join type
     *
     * @return string
     */
    protected function parseJoinType()
    {
        $joinType = Constants::JCR_JOIN_TYPE_INNER;
        $token = $this->scanner->fetchNextToken();

        switch ($token) {
            case 'JOIN':
                // Token already fetched, nothing to do
                break;
            case 'INNER':
                $this->scanner->fetchNextToken();
                break;
            case 'LEFT':
                $this->scanner->expectTokens(array('OUTER', 'JOIN'));
                $joinType = Constants::JCR_JOIN_TYPE_LEFT_OUTER;
                break;
            case 'RIGHT':
                $this->scanner->expectTokens(array('OUTER', 'JOIN'));
                $joinType = Constants::JCR_JOIN_TYPE_RIGHT_OUTER;
                break;
            default:
                throw new InvalidQueryException("Syntax error: Expected JOIN, INNER JOIN, RIGHT JOIN or LEFT JOIN in '{$this->sql2}'");
        }

        return $joinType;
    }

    /**
     * 6.7.7. JoinCondition
     * Parse an SQL2 join condition and return a JoinConditionInterface
     *
     * @return JoinConditionInterface
     */
    protected function parseJoinCondition()
    {
        $this->scanner->expectToken('ON');

        $token = $this->scanner->lookupNextToken();
        if ($this->scanner->tokenIs($token, 'ISSAMENODE')) {
            return $this->parseSameNodeJoinCondition();
        }

        if ($this->scanner->tokenIs($token, 'ISCHILDNODE')) {
            return $this->parseChildNodeJoinCondition();
        }

        if ($this->scanner->tokenIs($token, 'ISDESCENDANTNODE')) {
            return $this->parseDescendantNodeJoinCondition();
        }

        return $this->parseEquiJoin();
    }

    /**
     * 6.7.8. EquiJoinCondition
     * Parse an SQL2 equijoin condition and return a EquiJoinConditionInterface
     *
     * @return EquiJoinConditionInterface
     */
    protected function parseEquiJoin()
    {
        list($selectorName1, $prop1) = $this->parseIdentifier();
        $this->scanner->expectToken('=');
        list($selectorName2, $prop2) = $this->parseIdentifier();

        return $this->factory->equiJoinCondition($selectorName1, $prop1, $selectorName2, $prop2);
    }

    /**
     * 6.7.9 SameNodeJoinCondition
     * Parse an SQL2 same node join condition and return a SameNodeJoinConditionInterface
     *
     * @return SameNodeJoinConditionInterface
     */
    protected function parseSameNodeJoinCondition()
    {
        $this->scanner->expectTokens(array('ISSAMENODE', '('));
        $selectorName1 = $this->fetchTokenWithoutBrackets();
        $this->scanner->expectToken(',');
        $selectorName2 = $this->fetchTokenWithoutBrackets();

        $token = $this->scanner->lookupNextToken();
        if ($this->scanner->tokenIs($token, ',')) {
            $this->scanner->fetchNextToken(); // consume the coma
            $path = $this->parsePath();
        } else {
            $path = null;
        }

        $this->scanner->expectToken(')');

        return $this->factory->sameNodeJoinCondition($selectorName1, $selectorName2, $path);
    }

    /**
     * 6.7.10 ChildNodeJoinCondition
     * Parse an SQL2 child node join condition and return a ChildNodeJoinConditionInterface
     *
     * @return ChildNodeJoinConditionInterface
     */
    protected function parseChildNodeJoinCondition()
    {
        $this->scanner->expectTokens(array('ISCHILDNODE', '('));
        $child = $this->fetchTokenWithoutBrackets();
        $this->scanner->expectToken(',');
        $parent = $this->fetchTokenWithoutBrackets();
        $this->scanner->expectToken(')');

        return $this->factory->childNodeJoinCondition($child, $parent);
    }

    /**
     * 6.7.11 DescendantNodeJoinCondition
     * Parse an SQL2 descendant node join condition and return a DescendantNodeJoinConditionInterface
     *
     * @return DescendantNodeJoinConditionInterface
     */
    protected function parseDescendantNodeJoinCondition()
    {
        $this->scanner->expectTokens(array('ISDESCENDANTNODE', '('));
        $descendant = $this->fetchTokenWithoutBrackets();
        $this->scanner->expectToken(',');
        $parent = $this->fetchTokenWithoutBrackets();
        $this->scanner->expectToken(')');

        return $this->factory->descendantNodeJoinCondition($descendant, $parent);
    }

    /**
     * 6.7.13 And
     * 6.7.14 Or
     *
     * @return ConstraintInterface
     */
    protected function parseConstraint($lhs = null, $minprec = 0)
    {
        if ($lhs === null) {
            $lhs = $this->parsePrimaryConstraint();
        }

        $opprec = array(
            'OR' => 1,
            'AND' => 2,
        );

        $op = strtoupper($this->scanner->lookupNextToken());
        while (isset($opprec[$op]) && $opprec[$op] >= $minprec) {
            $this->scanner->fetchNextToken();

            $rhs = $this->parsePrimaryConstraint();

            $nextop = strtoupper($this->scanner->lookupNextToken());

            while (isset($opprec[$nextop]) && $opprec[$nextop] > $opprec[$op]) {
                $rhs = $this->parseConstraint($rhs, $opprec[$nextop]);
                $nextop = strtoupper($this->scanner->lookupNextToken());
            }

            switch ($op) {
                case 'AND':
                    $lhs = $this->factory->andConstraint($lhs, $rhs);
                    break;
                case 'OR':
                    $lhs = $this->factory->orConstraint($lhs, $rhs);
                    break;
                default:
                    // this only happens if the operator is
                    // in the $opprec-array but there is no
                    // "elseif"-branch here for this operator.
                    throw new \Exception(
                        "Internal error: No action is defined for operator '$op'"
                    );
            }

            $op = strtoupper($this->scanner->lookupNextToken());
        }

        return $lhs;
    }

    /**
     * 6.7.12 Constraint
     *
     * @return ConstraintInterface
     */
    protected function parsePrimaryConstraint()
    {
        $constraint = null;
        $token = $this->scanner->lookupNextToken();

        if ($this->scanner->tokenIs($token, 'NOT')) {
            // NOT
            $constraint = $this->parseNot();
        } elseif ($this->scanner->tokenIs($token, '(')) {
            // Grouping with parenthesis
            $this->scanner->expectToken('(');
            $constraint = $this->parseConstraint();
            $this->scanner->expectToken(')');
        } elseif ($this->scanner->tokenIs($token, 'CONTAINS')) {
            // Full Text Search
            $constraint = $this->parseFullTextSearch();
        } elseif ($this->scanner->tokenIs($token, 'ISSAMENODE')) {
            // SameNode
            $constraint = $this->parseSameNode();
        } elseif ($this->scanner->tokenIs($token, 'ISCHILDNODE')) {
            // ChildNode
            $constraint = $this->parseChildNode();
        } elseif ($this->scanner->tokenIs($token, 'ISDESCENDANTNODE')) {
            // DescendantNode
            $constraint = $this->parseDescendantNode();
        } else {
            // Is it a property existence?
            $next1 = $this->scanner->lookupNextToken(1);
            if ($this->scanner->tokenIs($next1, 'IS')) {
                $constraint = $this->parsePropertyExistence();
            } elseif ($this->scanner->tokenIs($next1, '.')) {
                $next2 = $this->scanner->lookupNextToken(3);
                if ($this->scanner->tokenIs($next2, 'IS')) {
                    $constraint = $this->parsePropertyExistence();
                }
            }

            if ($constraint === null) {
                // It's not a property existence neither, then it's a comparison
                $constraint = $this->parseComparison();
            }
        }

        // No constraint read,
        if ($constraint === null) {
            throw new InvalidQueryException("Syntax error: constraint expected in '{$this->sql2}'");
        }

        return $constraint;
    }

    /**
     * 6.7.15 Not
     *
     * @return NotInterface
     */
    protected function parseNot()
    {
        $this->scanner->expectToken('NOT');

        return $this->factory->notConstraint($this->parsePrimaryConstraint());
    }

    /**
     * 6.7.16 Comparison
     *
     * @return ComparisonInterface
     */
    protected function parseComparison()
    {
        $op1 = $this->parseDynamicOperand();

        if (null === $op1) {
            throw new InvalidQueryException("Syntax error: dynamic operator expected in '{$this->sql2}'");
        }

        $operator = $this->parseOperator();
        $op2 = $this->parseStaticOperand();

        return $this->factory->comparison($op1, $operator, $op2);
    }

    /**
     * 6.7.17 Operator
     *
     * @return string a constant from QueryObjectModelConstantsInterface
     */
    protected function parseOperator()
    {
        $token = $this->scanner->fetchNextToken();
        switch (strtoupper($token)) {
            case '=':
                return Constants::JCR_OPERATOR_EQUAL_TO;
            case '<>':
                return Constants::JCR_OPERATOR_NOT_EQUAL_TO;
            case '<':
                return Constants::JCR_OPERATOR_LESS_THAN;
            case '<=':
                return Constants::JCR_OPERATOR_LESS_THAN_OR_EQUAL_TO;
            case '>':
                return Constants::JCR_OPERATOR_GREATER_THAN;
            case '>=':
                return Constants::JCR_OPERATOR_GREATER_THAN_OR_EQUAL_TO;
            case 'LIKE':
                return Constants::JCR_OPERATOR_LIKE;
        }

        throw new InvalidQueryException("Syntax error: operator expected in '{$this->sql2}'");
    }

    /**
     * 6.7.18 PropertyExistence
     *
     * @return PropertyExistenceInterface
     */
    protected function parsePropertyExistence()
    {
        list($selectorName, $prop) = $this->parseIdentifier();

        $this->scanner->expectToken('IS');
        $token = $this->scanner->lookupNextToken();
        if ($this->scanner->tokenIs($token, 'NULL')) {
            $this->scanner->fetchNextToken();

            return $this->factory->notConstraint($this->factory->propertyExistence($selectorName, $prop));
        }

        $this->scanner->expectTokens(array('NOT', 'NULL'));

        return $this->factory->propertyExistence($selectorName, $prop);
    }

    /**
     * 6.7.19 FullTextSearch
     *
     * @return FullTextSearchInterface
     */
    protected function parseFullTextSearch()
    {
        $this->scanner->expectTokens(array('CONTAINS', '('));

        list($selectorName, $propertyName) = $this->parseIdentifier();
        $this->scanner->expectToken(',');
        $expression = $this->parseLiteral()->getLiteralValue();
        $this->scanner->expectToken(')');

        return $this->factory->fullTextSearch($selectorName, $propertyName, $expression);
    }

    /**
     * 6.7.20 SameNode
     */
    protected function parseSameNode()
    {
        $this->scanner->expectTokens(array('ISSAMENODE', '('));
        if ($this->scanner->tokenIs($this->scanner->lookupNextToken(1), ',')) {
            $selectorName = $this->scanner->fetchNextToken();
            $this->scanner->expectToken(',');
            $path = $this->parsePath();
        } else {
            $selectorName = $this->implicitSelectorName;
            $path = $this->parsePath();
        }
        $this->scanner->expectToken(')');

        return $this->factory->sameNode($selectorName, $path);
    }

    /**
     * 6.7.21 ChildNode
     */
    protected function parseChildNode()
    {
        $this->scanner->expectTokens(array('ISCHILDNODE', '('));
        if ($this->scanner->tokenIs($this->scanner->lookupNextToken(1), ',')) {
            $selectorName = $this->scanner->fetchNextToken();
            $this->scanner->expectToken(',');
            $path = $this->parsePath();
        } else {
            $selectorName = $this->implicitSelectorName;
            $path = $this->parsePath();
        }
        $this->scanner->expectToken(')');

        return $this->factory->childNode($selectorName, $path);
    }

    /**
     * 6.7.22 DescendantNode
     */
    protected function parseDescendantNode()
    {
        $this->scanner->expectTokens(array('ISDESCENDANTNODE', '('));
        if ($this->scanner->tokenIs($this->scanner->lookupNextToken(1), ',')) {
            $selectorName = $this->scanner->fetchNextToken();
            $this->scanner->expectToken(',');
            $path = $this->parsePath();
        } else {
            $selectorName = $this->implicitSelectorName;
            $path = $this->parsePath();
        }
        $this->scanner->expectToken(')');

        return $this->factory->descendantNode($selectorName, $path);
    }

    /**
     * Parse a JCR path consisting of either a simple path (a JCR name that contains
     * only SQL-legal characters) or a path (simple path or quoted path) enclosed in
     * square brackets. See JCR Spec § 6.7.23.
     *
     * 6.7.23. Path
     */
    protected function parsePath()
    {
        $path = $this->parseLiteral()->getLiteralValue();
        if (substr($path, 0, 1) === '[' && substr($path, -1) === ']') {
            $path = substr($path, 1, -1);
        }

        return $path;
    }

    /**
     * Parse an SQL2 static operand
     * 6.7.35 BindVariable
     * 6.7.36 Prefix
     *
     * @return StaticOperandInterface
     */
    protected function parseStaticOperand()
    {
        $token = $this->scanner->lookupNextToken();
        if (substr($token, 0, 1) === '$') {
            return $this->factory->bindVariable(substr($this->scanner->fetchNextToken(), 1));
        }

        return $this->parseLiteral();
    }

    /**
     * 6.7.26 DynamicOperand
     * 6.7.28 Length
     * 6.7.29 NodeName
     * 6.7.30 NodeLocalName
     * 6.7.31 FullTextSearchScore
     * 6.7.32 LowerCase
     * 6.7.33 UpperCase
     * Parse an SQL2 dynamic operand
     *
     * @return DynamicOperandInterface
     */
    protected function parseDynamicOperand()
    {
        $token = $this->scanner->lookupNextToken();

        if ($this->scanner->tokenIs($token, 'LENGTH')) {
            $this->scanner->fetchNextToken();
            $this->scanner->expectToken('(');
            $val = $this->parsePropertyValue();
            $this->scanner->expectToken(')');

            return $this->factory->length($val);
        }

        if ($this->scanner->tokenIs($token, 'NAME')) {
            $this->scanner->fetchNextToken();
            $this->scanner->expectToken('(');

            $token = $this->scanner->fetchNextToken();
            if ($this->scanner->tokenIs($token, ')')) {
                return $this->factory->nodeName($this->implicitSelectorName);
            }

            $this->scanner->expectToken(')');

            return $this->factory->nodeName($token);
        }

        if ($this->scanner->tokenIs($token, 'LOCALNAME')) {
            $this->scanner->fetchNextToken();
            $this->scanner->expectToken('(');

            $token = $this->scanner->fetchNextToken();
            if ($this->scanner->tokenIs($token, ')')) {
                return $this->factory->nodeLocalName($this->implicitSelectorName);
            }

            $this->scanner->expectToken(')');

            return $this->factory->nodeLocalName($token);
        }

        if ($this->scanner->tokenIs($token, 'SCORE')) {
            $this->scanner->fetchNextToken();
            $this->scanner->expectToken('(');

            $token = $this->scanner->fetchNextToken();
            if ($this->scanner->tokenIs($token, ')')) {
                return $this->factory->fullTextSearchScore($this->implicitSelectorName);
            }

            $this->scanner->expectToken(')');

            return $this->factory->fullTextSearchScore($token);
        }

        if ($this->scanner->tokenIs($token, 'LOWER')) {
            $this->scanner->fetchNextToken();
            $this->scanner->expectToken('(');
            $op = $this->parseDynamicOperand();
            $this->scanner->expectToken(')');

            return $this->factory->lowerCase($op);
        }

        if ($this->scanner->tokenIs($token, 'UPPER')) {
            $this->scanner->fetchNextToken();
            $this->scanner->expectToken('(');
            $op = $this->parseDynamicOperand();
            $this->scanner->expectToken(')');

            return $this->factory->upperCase($op);
        }

        return $this->parsePropertyValue();
    }

    /**
     * 6.7.27 PropertyValue
     * Parse an SQL2 property value
     *
     * @return PropertyValueInterface
     */
    protected function parsePropertyValue()
    {
        list($selectorName, $prop) = $this->parseIdentifier();

        return $this->factory->propertyValue($selectorName, $prop);
    }

    /**
     * 6.7.34 Literal
     * Parse an SQL2 literal value
     *
     * @return LiteralInterface
     */
    protected function parseLiteral()
    {
        $token = $this->scanner->fetchNextToken();

        $quoteString = false;
        if (substr($token, 0, 1) === '\'') {
            $quoteString = "'";
        } elseif (substr($token, 0, 1) === '"') {
            $quoteString = '"';
        }

        if ($quoteString) {
            while (substr($token, -1) !== $quoteString) {
                $nextToken = $this->scanner->fetchNextToken();
                if ('' === $nextToken) {
                    break;
                }
                $token .= $this->scanner->getPreviousDelimiter();
                $token .= $nextToken;
            }

            if (substr($token, -1) !== $quoteString) {
                throw new InvalidQueryException("Syntax error: unterminated quoted string $token in '{$this->sql2}'");
            }
            $token = substr($token, 1, -1);
            $token = str_replace('\\'.$quoteString, $quoteString, $token);
        } elseif (is_numeric($token)) {
            $token = strpos($token, '.') === false ? (int) $token : (float) $token;
        } elseif ($token == 'true') {
            $token = true;
        } elseif ($token == 'false') {
            $token = false;
        }

        return $this->factory->literal($token);
    }

    /**
     * 6.7.37 Ordering
     */
    protected function parseOrderings()
    {
        $orderings = array();
        $continue = true;
        while ($continue) {
            $orderings[] = $this->parseOrdering();
            if ($this->scanner->tokenIs($this->scanner->lookupNextToken(), ',')) {
                $this->scanner->expectToken(',');
            } else {
                $continue = false;
            }
        }

        return $orderings;
    }

    /**
     * 6.7.38 Order
     */
    protected function parseOrdering()
    {
        $operand = $this->parseDynamicOperand();
        $token = $this->scanner->lookupNextToken();

        if ($this->scanner->tokenIs($token, 'DESC')) {
            $this->scanner->expectToken('DESC');

            return $this->factory->descending($operand);
        }

        if ($this->scanner->tokenIs($token, 'ASC') || ',' === $token || '' === $token) {
            if ($this->scanner->tokenIs($token, 'ASC')) {
                $this->scanner->expectToken('ASC');
            }

            return $this->factory->ascending($operand);
        }

        throw new InvalidQueryException("Syntax error: invalid ordering in '{$this->sql2}'");
    }

    /**
     * 6.7.39 Column
     *
     * Scan the SQL2 columns definitions and return data arrays to convert to
     * columns once the FROM is parsed.
     *
     * @return array of array
     */
    protected function scanColumns()
    {
        $this->scanner->expectToken('SELECT');

        // Wildcard
        if ($this->scanner->lookupNextToken() === '*') {
            $this->scanner->fetchNextToken();

            return array();
        }

        $columns = array();
        $hasNext = true;

        while ($hasNext) {
            $columns[] = $this->scanColumn();

            // Are there more columns?
            if ($this->scanner->lookupNextToken() !== ',') {
                $hasNext = false;
            } else {
                $this->scanner->fetchNextToken();
            }

        }

        return $columns;
    }

    /**
     * Build the columns from the scanned column data.
     *
     * @param array $data
     *
     * @return ColumnInterface[]
     */
    protected function buildColumns($data)
    {
        $columns = array();
        foreach ($data as $col) {
            $columns[] = $this->buildColumn($col);
        }

        return $columns;
    }

    /**
     * Get the next token and make sure to remove the brackets if the token is
     * in the [ns:name] notation
     *
     * @return string
     */
    private function fetchTokenWithoutBrackets()
    {
        $token = $this->scanner->fetchNextToken();

        if (substr($token, 0, 1) === '[' && substr($token, -1) === ']') {
            // Remove brackets around the selector name
            $token = substr($token, 1, -1);
        }

        return $token;
    }

    /**
     * Parse something that is expected to be a property identifier.
     *
     * @param boolean $checkSelector whether we need to ensure a valid selector.
     *
     * @return array with selectorName and propertyName. If no selectorName is
     *      specified, defaults to $this->defaultSelectorName
     */
    private function parseIdentifier($checkSelector = true)
    {
        $token = $this->fetchTokenWithoutBrackets();

        // selector.property
        if ($this->scanner->lookupNextToken() === '.') {
            $selectorName = $token;
            $this->scanner->fetchNextToken();
            $propertyName = $this->fetchTokenWithoutBrackets();
        } else {
            $selectorName = null;
            $propertyName = $token;
        }

        if ($checkSelector) {
            $selectorName = $this->ensureSelectorName($selectorName);
        }

        return array($selectorName, $propertyName);
    }

    /**
     * Add a selector name to the known selector names.
     *
     * @param string $selectorName
     */
    protected function updateImplicitSelectorName($selectorName)
    {
        if (null === $this->implicitSelectorName) {
            $this->implicitSelectorName = $selectorName;
        } else {
            if (!is_array($this->implicitSelectorName)) {
                $this->implicitSelectorName = array($this->implicitSelectorName => $this->implicitSelectorName);
            }
            if (isset($this->implicitSelectorName[$selectorName])) {
                throw new InvalidQueryException("Selector $selectorName is already in use");
            }
            $this->implicitSelectorName[$selectorName] = $selectorName;
        }
    }

    /**
     * Ensure that the parsedName is a valid selector, or return the implicit
     * selector if its non-ambigous.
     *
     * @param string|null $parsedName
     *
     * @return string the selector to use
     *
     * @throws InvalidQueryException if there was no explicit selector and
     *      there is more than one selector available.
     */
    protected function ensureSelectorName($parsedName)
    {
        if (null !== $parsedName) {
            if (is_array($this->implicitSelectorName) && !isset($this->implicitSelectorName[$parsedName])
                || !is_array($this->implicitSelectorName) && $this->implicitSelectorName !== $parsedName
            ) {
                throw new InvalidQueryException("Unknown selector $parsedName in '{$this->sql2}'");
            }

            return $parsedName;
        }
        if (is_array($this->implicitSelectorName)) {
            throw new InvalidQueryException("Need an explicit selector name in join queries");
        }

        return $this->implicitSelectorName;
    }

    /**
     * Scan a single SQL2 column definition and return an array of information
     *
     * @return array
     */
    protected function scanColumn()
    {
        list($selectorName, $propertyName) = $this->parseIdentifier(false);

        // AS name
        if ($this->scanner->tokenIs($this->scanner->lookupNextToken(), 'AS')) {
            $this->scanner->fetchNextToken();
            $columnName = $this->scanner->fetchNextToken();
        } else {
            $columnName = $propertyName;
        }

        return array($selectorName, $propertyName, $columnName);
    }

    /**
     * Build a single SQL2 column definition
     *
     * @return ColumnInterface
     */
    protected function buildColumn($data)
    {
        list($selectorName, $propertyName, $columnName) = $data;
        $selectorName = $this->ensureSelectorName($selectorName);

        return $this->factory->column($selectorName, $propertyName, $columnName);
    }
}

<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

/**
 * Generate SQL2 statements.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Sql2Generator extends BaseSqlGenerator
{
    /**
     * Selector ::= nodeTypeName ['AS' selectorName]
     * nodeTypeName ::= Name
     *
     * @param string      $nodeTypeName The node type of the selector. If it
     *      does not contain starting and ending brackets ([]) they will be
     *      added automatically.
     * @param string|null $selectorName The selector name. If it is different than
     *      the nodeTypeName, the alias is declared.
     *
     * @return string
     */
    public function evalSelector($nodeTypeName, $selectorName = null)
    {
        $sql2 = $this->addBracketsIfNeeded($nodeTypeName);

        if (null !== $selectorName && $nodeTypeName !== $selectorName) {
            // if the selector name is the same as the type name, this is implicit for sql2
            $sql2 .= ' AS ' . $selectorName;
        }

        return $sql2;
    }

    /**
     * Join ::= left [JoinType] 'JOIN' right 'ON' JoinCondition
     *    // If JoinType is omitted INNER is assumed.
     * left ::= Source
     * right ::= Source
     *
     * @param string $left
     * @param string $right
     * @param string $joinCondition
     * @param string $joinType
     *
     * @return string
     */
    public function evalJoin($left, $right, $joinCondition, $joinType = '')
    {
        return "$left {$joinType}JOIN $right ON $joinCondition";
    }

    /**
     * JoinType ::= Inner | LeftOuter | RightOuter
     * Inner ::= 'INNER'
     * LeftOuter ::= 'LEFT OUTER'
     * RightOuter ::= 'RIGHT OUTER'
     *
     * @param  string $joinType
     * @return string
     */
    public function evalJoinType($joinType)
    {
        switch ($joinType) {
            case Constants::JCR_JOIN_TYPE_INNER:
                return 'INNER ';
            case Constants::JCR_JOIN_TYPE_LEFT_OUTER:
                return 'LEFT OUTER ';
            case Constants::JCR_JOIN_TYPE_RIGHT_OUTER:
                return 'RIGHT OUTER ';
        }

        return $joinType;
    }

    /**
     * EquiJoinCondition ::= selector1Name'.'property1Name '='
     *                       selector2Name'.'property2Name
     *   selector1Name ::= selectorName
     *   selector2Name ::= selectorName
     *   property1Name ::= propertyName
     *   property2Name ::= propertyName
     *
     * @param  string $sel1Name
     * @param  string $prop1Name
     * @param  string $sel2Name
     * @param  string $prop2Name
     * @return string
     */
    public function evalEquiJoinCondition($sel1Name, $prop1Name, $sel2Name, $prop2Name)
    {
        return $this->evalPropertyValue($prop1Name, $sel1Name) . '=' .$this->evalPropertyValue($prop2Name, $sel2Name);
    }

    /**
     * SameNodeJoinCondition ::=
     *   'ISSAMENODE(' selector1Name ','
     *                  selector2Name
     *                  [',' selector2Path] ')'
     *   selector2Path ::= Path
     *
     * @param  string $sel1Name
     * @param  string $sel2Name
     * @param  string $sel2Path
     * @return string
     */
    public function evalSameNodeJoinCondition($sel1Name, $sel2Name, $sel2Path = null)
    {
        $sql2 = 'ISSAMENODE('
            . $this->addBracketsIfNeeded($sel1Name) . ', '
            . $this->addBracketsIfNeeded($sel2Name)
        ;
        if (null !== $sel2Path) {
            $sql2 .= ', ' . $sel2Path;
        }
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * ChildNodeJoinCondition ::=
     *   'ISCHILDNODE(' childSelectorName ','
     *                  parentSelectorName ')'
     *   childSelectorName ::= selectorName
     *   parentSelectorName ::= selectorName
     *
     * @param  string $childSelectorName
     * @param  string $parentSelectorName
     * @return string
     */
    public function evalChildNodeJoinCondition($childSelectorName, $parentSelectorName)
    {
        return 'ISCHILDNODE('
            . $this->addBracketsIfNeeded($childSelectorName) . ', '
            . $this->addBracketsIfNeeded($parentSelectorName) . ')'
        ;
    }

    /**
     * DescendantNodeJoinCondition ::=
     *   'ISDESCENDANTNODE(' descendantSelectorName ','
     *                       ancestorSelectorName ')'
     *   descendantSelectorName ::= selectorName
     *   ancestorSelectorName ::= selectorName
     *
     * @param  string $descendantSelectorName
     * @param  string $ancestorselectorName
     * @return string
     */
    public function evalDescendantNodeJoinCondition($descendantSelectorName, $ancestorselectorName)
    {
        return 'ISDESCENDANTNODE('
            . $this->addBracketsIfNeeded($descendantSelectorName) . ', '
            . $this->addBracketsIfNeeded($ancestorselectorName) . ')'
        ;
    }

    /**
     * SameNode ::= 'ISSAMENODE(' [selectorName ','] Path ')'
     *
     * @param string $path
     * @param string $selectorName
     */
    public function evalSameNode($path, $selectorName = null)
    {
        $sql2 = 'ISSAMENODE(';
        $sql2 .= null === $selectorName ? $path : $this->addBracketsIfNeeded($selectorName) . ', ' . $path;
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * SameNode ::= 'ISCHILDNODE(' [selectorName ','] Path ')'
     *
     * @param string $path
     * @param string $selectorName
     */
    public function evalChildNode($path, $selectorName = null)
    {
        $sql2 = 'ISCHILDNODE(';
        $sql2 .= null === $selectorName ? $path : $this->addBracketsIfNeeded($selectorName) . ', ' . $path;
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * SameNode ::= 'ISDESCENDANTNODE(' [selectorName ','] Path ')'
     *
     * @param string $path
     * @param string $selectorName
     */
    public function evalDescendantNode($path, $selectorName = null)
    {
        $sql2 = 'ISDESCENDANTNODE(';
        $sql2 .= null === $selectorName ? $path : $this->addBracketsIfNeeded($selectorName) . ', ' . $path;
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * PropertyExistence ::=
     *   selectorName'.'propertyName 'IS NOT NULL' |
     *   propertyName 'IS NOT NULL'    If only one
     *                                 selector exists in
     *                                 this query
     *
     * @param $selectorName
     * @param $propertyName
     *
     * @return string
     */
    public function evalPropertyExistence($selectorName, $propertyName)
    {
        return $this->evalPropertyValue($propertyName, $selectorName) . " IS NOT NULL";
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' ([selectorName'.']propertyName |
     *                    selectorName'.*') ','
     *                    FullTextSearchExpression ')'
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''
     * @param  string $selectorName     unusued
     * @param  string $searchExpression
     * @param  string $propertyName
     * @return string
     */
    public function evalFullTextSearch($selectorName, $searchExpression, $propertyName = null)
    {
        $propertyName = $propertyName ?: '*';

        $sql2 = 'CONTAINS(';
        $sql2 .= $this->evalPropertyValue($propertyName, $selectorName);
        $sql2 .= ', ' . $searchExpression . ')';

        return $sql2;
    }

    /**
     * Length ::= 'LENGTH(' PropertyValue ')'
     *
     * @param  string $propertyValue
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
        return "NAME($selectorValue)";
    }

    /**
     * NodeLocalName ::= 'LOCALNAME(' [selectorName] ')'
     *
     * @param string $selectorValue
     */
    public function evalNodeLocalName($selectorValue = null)
    {
        return "LOCALNAME($selectorValue)";
    }

    /**
     * FullTextSearchScore ::= 'SCORE(' [selectorName] ')'
     *
     * @param string $selectorValue
     */
    public function evalFullTextSearchScore($selectorValue = null)
    {
        return "SCORE($selectorValue)";
    }

    /**
     * PropertyValue ::= [selectorName'.'] propertyName     // If only one selector exists
     *
     * @param string $propertyName
     * @param string $selectorName
     */
    public function evalPropertyValue($propertyName, $selectorName = null)
    {
        $sql2 = null !== $selectorName ? $this->addBracketsIfNeeded($selectorName) . '.' : '';
        if (false !== strpos($propertyName, ':')) {
            $propertyName = "[$propertyName]";
        }
        $sql2 .= $propertyName;

        return $sql2;
    }

    /**
     * columns ::= (Column ',' {Column}) | '*'
     *
     * @param $columns
     *
     * @return string
     */
    public function evalColumns($columns)
    {
        if (count($columns) === 0) {
            return '*';
        }

        $sql2 = '';
        foreach ($columns as $column) {
            if ($sql2 !== '') {
                $sql2 .= ', ';
            }

            $sql2 .= $column;
        }

        return $sql2;
    }

    /**
     * Column ::= ([selectorName'.']propertyName
     *             ['AS' columnName]) |
     *            (selectorName'.*')    // If only one selector exists
     * selectorName ::= Name
     * propertyName ::= Name
     * columnName ::= Name
     *
     * @param string $selectorName
     * @param string $propertyName
     * @param string $colname
     *
     * @return string
     */
    public function evalColumn($selectorName, $propertyName = null, $colname = null)
    {
        $sql2 = '';
        if (null !== $selectorName && null === $propertyName && null === $colname) {
            $sql2 .= $this->addBracketsIfNeeded($selectorName) . '.*';
        } else {
            $sql2 .= $this->evalPropertyValue($propertyName, $selectorName);
            if (null !== $colname && $colname !== $propertyName) {
                // if the column name is the same as the property name, this is implicit for sql2
                $sql2 .=  ' AS ' . $colname;
            }
        }

        return $sql2;
    }

    /**
     * Path ::= '[' quotedPath ']' | '[' simplePath ']' | simplePath
     * quotedPath ::= A JCR Path that contains non-SQL-legal characters
     * simplePath ::= A JCR Name that contains only SQL-legal characters
     *
     * @param  string $path
     * @return string
     */
    public function evalPath($path)
    {
        if ($path) {
            $sql2 = $path;
            // only ensure proper quoting if the user did not quote himself, we trust him to get it right if he did.
            if (substr($path, 0,1) !== '[' && substr($path, -1) !== ']') {
                if (false !== strpos($sql2, ' ') || false !== strpos($sql2, '.')) {
                    $sql2 = '"' . $sql2 . '"';
                }
                $sql2 = '[' . $sql2 . ']';
            }

            return $sql2;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * CastLiteral ::= 'CAST(' UncastLiteral ' AS ' PropertyType ')'
     */
    public function evalCastLiteral($literal, $type)
    {
        return "CAST('$literal' AS $type)";
    }

    /**
     * Add square brackets around a selector if needed
     *
     * @param string $selector
     *
     * @return string $selector guaranteed to have [] around it if needed
     */
    private function addBracketsIfNeeded($selector)
    {
        if (substr($selector, 0, 1) !== '['
            && substr($selector, -1) !== ']'
            && false !== strpos($selector, ':')
        ) {
            return "[$selector]";
        }

        return $selector;
    }
}

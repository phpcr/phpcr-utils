<?php

declare(strict_types=1);

namespace PHPCR\Util\QOM;

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
     * nodeTypeName ::= Name.
     *
     * @param string      $nodeTypeName The node type of the selector. If it
     *                                  does not contain starting and ending brackets ([]) they will be
     *                                  added automatically.
     * @param string|null $selectorName The selector name. If it is different than
     *                                  the nodeTypeName, the alias is declared.
     */
    public function evalSelector(string $nodeTypeName, string $selectorName = null): string
    {
        $sql2 = $this->addBracketsIfNeeded($nodeTypeName);

        if (null !== $selectorName && $nodeTypeName !== $selectorName) {
            // if the selector name is the same as the type name, this is implicit for sql2
            $sql2 .= ' AS '.$selectorName;
        }

        return $sql2;
    }

    /**
     * Join ::= left [JoinType] 'JOIN' right 'ON' JoinCondition
     *    // If JoinType is omitted INNER is assumed.
     * left ::= Source
     * right ::= Source.
     */
    public function evalJoin(string $left, string $right, string $joinCondition, string $joinType = ''): string
    {
        return "$left {$joinType}JOIN $right ON $joinCondition";
    }

    /**
     * JoinType ::= Inner | LeftOuter | RightOuter
     * Inner ::= 'INNER'
     * LeftOuter ::= 'LEFT OUTER'
     * RightOuter ::= 'RIGHT OUTER'.
     */
    public function evalJoinType(string $joinType): string
    {
        return match ($joinType) {
            Constants::JCR_JOIN_TYPE_INNER => 'INNER ',
            Constants::JCR_JOIN_TYPE_LEFT_OUTER => 'LEFT OUTER ',
            Constants::JCR_JOIN_TYPE_RIGHT_OUTER => 'RIGHT OUTER ',
            default => $joinType,
        };
    }

    /**
     * EquiJoinCondition ::= selector1Name'.'property1Name '='
     *                       selector2Name'.'property2Name
     *   selector1Name ::= selectorName
     *   selector2Name ::= selectorName
     *   property1Name ::= propertyName
     *   property2Name ::= propertyName.
     */
    public function evalEquiJoinCondition(string $sel1Name, string $prop1Name, string $sel2Name, string $prop2Name): string
    {
        return $this->evalPropertyValue($prop1Name, $sel1Name).'='.$this->evalPropertyValue($prop2Name, $sel2Name);
    }

    /**
     * SameNodeJoinCondition ::=
     *   'ISSAMENODE(' selector1Name ','
     *                  selector2Name
     *                  [',' selector2Path] ')'
     *   selector2Path ::= Path.
     */
    public function evalSameNodeJoinCondition(string $sel1Name, string $sel2Name, string $sel2Path = null): string
    {
        $sql2 = 'ISSAMENODE('
            .$this->addBracketsIfNeeded($sel1Name).', '
            .$this->addBracketsIfNeeded($sel2Name);
        if (null !== $sel2Path) {
            $sql2 .= ', '.$sel2Path;
        }
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * ChildNodeJoinCondition ::=
     *   'ISCHILDNODE(' childSelectorName ','
     *                  parentSelectorName ')'
     *   childSelectorName ::= selectorName
     *   parentSelectorName ::= selectorName.
     */
    public function evalChildNodeJoinCondition(string $childSelectorName, string $parentSelectorName): string
    {
        return 'ISCHILDNODE('
            .$this->addBracketsIfNeeded($childSelectorName).', '
            .$this->addBracketsIfNeeded($parentSelectorName).')';
    }

    /**
     * DescendantNodeJoinCondition ::=
     *   'ISDESCENDANTNODE(' descendantSelectorName ','
     *                       ancestorSelectorName ')'
     *   descendantSelectorName ::= selectorName
     *   ancestorSelectorName ::= selectorName.
     */
    public function evalDescendantNodeJoinCondition(string $descendantSelectorName, string $ancestorselectorName): string
    {
        return 'ISDESCENDANTNODE('
            .$this->addBracketsIfNeeded($descendantSelectorName).', '
            .$this->addBracketsIfNeeded($ancestorselectorName).')';
    }

    /**
     * SameNode ::= 'ISSAMENODE(' [selectorName ','] Path ')'.
     */
    public function evalSameNode(string $path, string $selectorName = null): string
    {
        $sql2 = 'ISSAMENODE(';
        $sql2 .= null === $selectorName ? $path : $this->addBracketsIfNeeded($selectorName).', '.$path;
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * SameNode ::= 'ISCHILDNODE(' [selectorName ','] Path ')'.
     */
    public function evalChildNode(string $path, string $selectorName = null): string
    {
        $sql2 = 'ISCHILDNODE(';
        $sql2 .= null === $selectorName ? $path : $this->addBracketsIfNeeded($selectorName).', '.$path;
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * SameNode ::= 'ISDESCENDANTNODE(' [selectorName ','] Path ')'.
     */
    public function evalDescendantNode(string $path, string $selectorName = null): string
    {
        $sql2 = 'ISDESCENDANTNODE(';
        $sql2 .= null === $selectorName ? $path : $this->addBracketsIfNeeded($selectorName).', '.$path;
        $sql2 .= ')';

        return $sql2;
    }

    /**
     * PropertyExistence ::=
     *   selectorName'.'propertyName 'IS NOT NULL' |
     *   propertyName 'IS NOT NULL'    If only one
     *                                 selector exists in
     *                                 this query.
     */
    public function evalPropertyExistence(?string $selectorName, string $propertyName): string
    {
        return $this->evalPropertyValue($propertyName, $selectorName).' IS NOT NULL';
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' ([selectorName'.']propertyName |
     *                    selectorName'.*') ','
     *                    FullTextSearchExpression ')'
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''.
     */
    public function evalFullTextSearch(string $selectorName, string $searchExpression, string $propertyName = null): string
    {
        $propertyName = $propertyName ?: '*';

        $sql2 = 'CONTAINS(';
        $sql2 .= $this->evalPropertyValue($propertyName, $selectorName);
        $sql2 .= ', '.$searchExpression.')';

        return $sql2;
    }

    /**
     * Length ::= 'LENGTH(' PropertyValue ')'.
     */
    public function evalLength(string $propertyValue): string
    {
        return "LENGTH($propertyValue)";
    }

    /**
     * NodeName ::= 'NAME(' [selectorName] ')'.
     */
    public function evalNodeName(string $selectorValue = null): string
    {
        return "NAME($selectorValue)";
    }

    /**
     * NodeLocalName ::= 'LOCALNAME(' [selectorName] ')'.
     */
    public function evalNodeLocalName(string $selectorValue = null): string
    {
        return "LOCALNAME($selectorValue)";
    }

    /**
     * FullTextSearchScore ::= 'SCORE(' [selectorName] ')'.
     */
    public function evalFullTextSearchScore(string $selectorValue = null): string
    {
        return "SCORE($selectorValue)";
    }

    /**
     * PropertyValue ::= [selectorName'.'] propertyName     // If only one selector exists.
     */
    public function evalPropertyValue(string $propertyName, string $selectorName = null): string
    {
        $sql2 = null !== $selectorName ? $this->addBracketsIfNeeded($selectorName).'.' : '';
        if ('*' !== $propertyName && !str_starts_with($propertyName, '[')) {
            $propertyName = "[$propertyName]";
        }
        $sql2 .= $propertyName;

        return $sql2;
    }

    /**
     * columns ::= (Column ',' {Column}) | '*'.
     */
    public function evalColumns(iterable $columns): string
    {
        if ((!is_array($columns) && !$columns instanceof \Countable)
            || 0 === count($columns)
        ) {
            return '*';
        }

        $sql2 = '';
        foreach ($columns as $column) {
            if ('' !== $sql2) {
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
     * columnName ::= Name.
     */
    public function evalColumn(string $selectorName, string $propertyName = null, string $colname = null): string
    {
        $sql2 = '';
        if (null !== $selectorName && null === $propertyName && null === $colname) {
            $sql2 .= $this->addBracketsIfNeeded($selectorName).'.*';
        } else {
            $sql2 .= $this->evalPropertyValue($propertyName, $selectorName);
            if (null !== $colname && $colname !== $propertyName) {
                // if the column name is the same as the property name, this is implicit for sql2
                $sql2 .= ' AS '.$colname;
            }
        }

        return $sql2;
    }

    /**
     * Path ::= '[' quotedPath ']' | '[' simplePath ']' | simplePath
     * quotedPath ::= A JCR Path that contains non-SQL-legal characters
     * simplePath ::= A JCR Name that contains only SQL-legal characters.
     */
    public function evalPath(string $path): string
    {
        if (!$path) {
            return $path;
        }
        $sql2 = $path;
        // only ensure proper quoting if the user did not quote himself, we trust him to get it right if he did.
        if (!str_starts_with($path, '[') && !str_ends_with($path, ']')) {
            if (str_contains($sql2, ' ') || str_contains($sql2, '.')) {
                $sql2 = '"'.$sql2.'"';
            }
            $sql2 = '['.$sql2.']';
        }

        return $sql2;
    }

    /**
     * {@inheritdoc}
     *
     * CastLiteral ::= 'CAST(' UncastLiteral ' AS ' PropertyType ')'
     */
    public function evalCastLiteral(string $literal, string $type): string
    {
        return "CAST('$literal' AS $type)";
    }

    /**
     * Add square brackets around a selector if needed.
     *
     * @return string $selector guaranteed to have [] around it if needed
     */
    private function addBracketsIfNeeded(string $selector): string
    {
        if (!str_starts_with($selector, '[')
            && !str_ends_with($selector, ']')
            && str_contains($selector, ':')
        ) {
            return "[$selector]";
        }

        return $selector;
    }
}

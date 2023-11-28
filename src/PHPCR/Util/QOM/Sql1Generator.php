<?php

declare(strict_types=1);

namespace PHPCR\Util\QOM;

/**
 * Generate SQL1 statements.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class Sql1Generator extends BaseSqlGenerator
{
    /**
     * Selector ::= nodeTypeName
     * nodeTypeName ::= Name.
     *
     * @param string      $nodeTypeName The node type of the selector. If it does not contain starting and ending brackets ([]) they will be added automatically
     * @param string|null $selectorName (unused)
     */
    public function evalSelector(string $nodeTypeName, string $selectorName = null): string
    {
        return $nodeTypeName;
    }

    /**
     * Helper method to emulate descendant with LIKE query on path property.
     */
    protected function getPathForDescendantQuery(string $path): string
    {
        if ('/' === $path) {
            $sql1 = '/%';
        } else {
            $path = trim($path, "\"'/");
            $sql1 = '/'.str_replace('/', '[%]/', $path);
            $sql1 .= '[%]/%';
        }

        return $sql1;
    }

    /**
     * SameNode ::= 'jcr:path like Path/% and not jcr:path like Path/%/%'.
     */
    public function evalChildNode(string $path, string $selectorName = null): string
    {
        $path = $this->getPathForDescendantQuery($path);
        $sql1 = "jcr:path LIKE '".$path."'";
        $sql1 .= " AND NOT jcr:path LIKE '".$path."/%'";

        return $sql1;
    }

    /**
     * Emulate descendant query with LIKE query.
     *
     * @param string|null $selectorName Unused
     */
    public function evalDescendantNode(string $path, string $selectorName = null): string
    {
        $path = $this->getPathForDescendantQuery($path);

        return "jcr:path LIKE '".$path."'";
    }

    /**
     * PropertyExistence ::=
     *   propertyName 'IS NOT NULL'.
     *
     * @param string $selectorName declared to simplifiy interface - as there
     *                             are no joins in SQL1 there is no need for a selector
     */
    public function evalPropertyExistence(?string $selectorName, string $propertyName): string
    {
        return $propertyName.' IS NOT NULL';
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' (propertyName | '*') ') ','
     *                    FullTextSearchExpression ')'
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''.
     *
     * @param string $selectorName unusued
     */
    public function evalFullTextSearch(string $selectorName, string $searchExpression, string $propertyName = null): string
    {
        $propertyName = $propertyName ?: '*';

        $sql1 = 'CONTAINS(';
        $sql1 .= $propertyName;
        $sql1 .= ', '.$searchExpression.')';

        return $sql1;
    }

    /**
     * columns ::= (Column ',' {Column}) | '*'.
     */
    public function evalColumns(iterable $columns): string
    {
        if ((!is_array($columns) && !$columns instanceof \Countable)
            || 0 === count($columns)
        ) {
            return 's';
        }

        $sql1 = '';
        foreach ($columns as $column) {
            if ('' !== $sql1) {
                $sql1 .= ', ';
            }

            $sql1 .= $column;
        }

        return $sql1;
    }

    /**
     * PropertyValue ::= propertyName.
     *
     * @param string|null $selectorName unused in SQL1
     */
    public function evalPropertyValue(string $propertyName, string $selectorName = null): string
    {
        return $propertyName;
    }

    /**
     * Column ::= (propertyName)
     * propertyName ::= Name.
     *
     * No support for column name ('AS' columnName) in SQL1
     *
     * @param string      $selectorName unused in SQL1
     * @param string|null $colname      unused in SQL1
     */
    public function evalColumn(string $selectorName, string $propertyName = null, string $colname = null): string
    {
        return $propertyName;
    }

    /**
     * Path ::= simplePath
     * simplePath ::= A JCR Name that contains only SQL-legal characters.
     */
    public function evalPath(string $path): string
    {
        return $path;
    }

    /**
     * {@inheritdoc}
     *
     * No explicit support, do some tricks where possible.
     */
    public function evalCastLiteral(string $literal, string $type): string
    {
        switch ($type) {
            case 'DATE':
                return "TIMESTAMP '$literal'";
            case 'LONG':
                return $literal;
            case 'DOUBLE':
                if ((int) $literal == $literal) {
                    return $literal.'.0';
                }

                return $literal;
        }

        return "'$literal'";
    }
}

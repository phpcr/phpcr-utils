<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;

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
     * nodeTypeName ::= Name
     *
     * @param  string $nodeTypeName The node type of the selector. If it does not contain starting and ending brackets ([]) they will be added automatically
     * @param  string $selectorName (unused)
     * @return string
     */
    public function evalSelector($nodeTypeName, $selectorName = null)
    {
        return $nodeTypeName;
    }

    /**
     * Helper method to emulate descendant with LIKE query on path property.
     *
     * @param $path
     *
     * @return string
     */
    protected function getPathForDescendantQuery($path)
    {
        if ($path == '/') {
            $sql1 = '/%';
        } else {
            $path = trim($path,"\"'/");
            $sql1 = "/" . str_replace("/","[%]/",$path) ;
            $sql1 .= "[%]/%";
        }

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
     * Emulate descendant query with LIKE query
     *
     * @param string $path
     *
     * @return string
     */
    public function evalDescendantNode($path)
    {
        $path = $this->getPathForDescendantQuery($path);
        $sql1 = "jcr:path LIKE '" . $path . "'";

        return $sql1;
    }

    /**
     * PropertyExistence ::=
     *   propertyName 'IS NOT NULL'

     * @param string $selectorName declared to simplifiy interface - as there
     *      are no joins in SQL1 there is no need for a selector.
     * @param string $propertyName
     *
     * @return string
     */
    public function evalPropertyExistence($selectorName, $propertyName)
    {
        return $propertyName . " IS NOT NULL";
    }

    /**
     * FullTextSearch ::=
     *       'CONTAINS(' (propertyName | '*') ') ','
     *                    FullTextSearchExpression ')'
     * FullTextSearchExpression ::= BindVariable | ''' FullTextSearchLiteral '''
     *
     * @param  string $selectorName     unusued
     * @param  string $searchExpression
     * @param  string $propertyName
     * @return string
     */
    public function evalFullTextSearch($selectorName, $searchExpression, $propertyName = null)
    {
        $propertyName = $propertyName ? : '*';

        $sql1 = 'CONTAINS(';
        $sql1 .= $propertyName;
        $sql1 .= ', ' . $searchExpression . ')';

        return $sql1;
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

    /**
     * PropertyValue ::= propertyName
     *
     * @param string $propertyName
     * @param string $selectorName unused in SQL1
     */
    public function evalPropertyValue($propertyName, $selectorName = null)
    {
        return $propertyName;
    }


    /**
     * Column ::= (propertyName)
     * propertyName ::= Name
     *
     * No support for column name ('AS' columnName) in SQL1
     *
     * @param string $selectorName unused in SQL1
     * @param string $propertyName
     *
     * @return string
     */

    public function evalColumn($selectorName = null, $propertyName = null)
    {
        return $propertyName;
    }

    /**
     * Path ::= simplePath
     * simplePath ::= A JCR Name that contains only SQL-legal characters
     *
     * @param  string $path
     * @return string
     */
    public function evalPath($path)
    {
        return $path;
    }

    /**
     * {@inheritDoc}
     *
     * No explicit support, do some tricks where possible.
     */
    public function evalCastLiteral($literal, $type)
    {
        switch ($type) {
            case 'DATE':
                return "TIMESTAMP '$literal'";
            case 'LONG':
                return $literal;
            case 'DOUBLE':
                if ((int) $literal == $literal) {
                    return $literal .".0";
                }

                return $literal;
        }

        return "'$literal'";
    }
}

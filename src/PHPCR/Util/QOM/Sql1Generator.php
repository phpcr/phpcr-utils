<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface as Constants;

/**
 * Generate SQL1 statements
 *
 * TODO: is eval... the best name for the functions here?
 */
class Sql1Generator extends BaseSqlGenerator
{

    /**
     * Selector ::= nodeTypeName
     * nodeTypeName ::= Name
     *
     * @param string $nodeTypeName The node type of the selector. If it does not contain starting and ending brackets ([]) they will be added automatically
     * @param string $selectorName (unused)
     * @return string
     */
    public function evalSelector($nodeTypeName, $selectorName = null)
    {
        return $nodeTypeName;
    }

    protected function getPathForDescendantQuery($path)
    {
        $path = trim($path,"\"'/");
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
     * @param string $selectorName unusued
     * @param string $searchExpression
     * @param string $ropertyName
     * @return string
     */
    public function evalFullTextSearch($selectorName, $searchExpression, $propertyName = null)
    {
        $sql1 = 'CONTAINS(';
        $sql1 .= is_null($propertyName) ? '*' : $propertyName;
        $sql1 .= ', ' . $searchExpression . ')';

        return $sql1;
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

    /**
     * PropertyValue ::= propertyName
     *
     * @param string $propertyName
     */
    public function evalPropertyValue($propertyName, $selectorName = null)
    {
        return $propertyName;
    }


    public function evalColumn($selecor = null, $property = null)
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

    /**
     * @param string $literal
     * @param string $type
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

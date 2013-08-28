<?php

namespace PHPCR\Util\CND\Writer;

use PHPCR\NamespaceRegistryInterface;
use PHPCR\PropertyType;
use PHPCR\Version\OnParentVersionAction;
use PHPCR\NodeType\NodeDefinitionInterface;
use PHPCR\NodeType\NodeTypeDefinitionInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;
use PHPCR\NodeType\NodeTypeTemplateInterface;
use PHPCR\NodeType\PropertyDefinitionInterface;

/**
 * Generator for JCR-2.0 CND files.
 *
 * Walk an array of node types and generate the CND file for them, including
 * the namespaces.
 *
 * @see http://www.day.com/specs/jcr/2.0/25_Appendix.html#25.2.3 CND Grammar
 * @see http://jackrabbit.apache.org/node-type-notation.html
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author David Buchmann <david@liip.ch>
 */
class CndWriter
{
    /**
     * @var NamespaceRegistryInterface
     */
    private $ns;

    /** @var array hashmap of prefix => namespace uri */
    private $namespaces = array();

    /**
     * @param NodeTypeManagerInterface $ntm
     */
    public function __construct(NamespaceRegistryInterface $ns)
    {
        $this->ns = $ns;
    }

    /**
     * Parse a file with CND statements.
     *
     * @param NodeTypeTemplateInterface[] $nodeTypes
     *
     * @return string with declarations for all non-system namespaces and for
     *      all node types in that array.
     */
    public function writeString(array $nodeTypes)
    {
        $cnd = '';
        foreach ($nodeTypes as $nodeType) {
            $cnd .= $this->writeNodeType($nodeType);
        }

        return $this->writeNamespaces() . $cnd;
    }

    /**
     * Generate the namespace mapping of all encountered namespaces.
     *
     * NamespaceMapping ::= '<' Prefix '=' Uri '>'
     * Prefix ::= String
     * Uri ::= String
     */
    protected function writeNamespaces()
    {
        $ns = '';
        foreach ($this->namespaces as $prefix => $uri) {
            $ns .= "<$prefix=$uri>\n";
        }

        return $ns;
    }

    private function checkNamespace($name)
    {
        if (false === strpos($name, ':')) {
            return;
        }
        list($prefix) = explode(':', $name);

        // namespace registry will throw exception if namespace prefix not found
        $this->namespaces[$prefix] = "'" . $this->ns->getURI($prefix) . "'";
    }

    /**
     * A node type definition consists of a node type name followed by an optional
     * supertypes block, an optional node type attributes block and zero or more
     * blocks, each of which is either a property or child node definition.
     *
     *      NodeTypeDef ::= NodeTypeName [Supertypes]
     *          [NodeTypeAttribute {NodeTypeAttribute}]
     *          {PropertyDef | ChildNodeDef}
     */
    protected function writeNodeType(NodeTypeDefinitionInterface $nodeType)
    {
        $this->checkNamespace($nodeType->getName());
        $s = '[' . $nodeType->getName() . ']';
        if ($superTypes = $nodeType->getDeclaredSupertypeNames()) {
            foreach ($superTypes as $superType) {
                $this->checkNamespace($superType);
            }
            $s .= ' > ' . implode(', ', $superTypes);
        }
        $s .= "\n";

        $attributes = '';

        if ($nodeType->hasOrderableChildNodes()) {
            $attributes .= 'orderable ';
        }
        if ($nodeType->isMixin()) {
            $attributes .= 'mixin ';
        }
        if ($nodeType->isAbstract()) {
            $attributes .= 'abstract ';
        }
        $attributes .= $nodeType->isQueryable() ? 'query ' : 'noquery ';
        if ($nodeType->getPrimaryItemName()) {
            $attributes .= 'primaryitem ' . $nodeType->getPrimaryItemName() . ' ';
        }
        if ($attributes) {
            $s .= trim($attributes) . "\n";
        }

        $s .= $this->writeProperties($nodeType->getDeclaredPropertyDefinitions());

        $s .= $this->writeChildren($nodeType->getDeclaredChildNodeDefinitions());

        return $s;
    }

    private function writeProperties($properties)
    {
        if (null === $properties) {
            // getDeclaredPropertyDefinitions is allowed to return null on
            // newly created node type definitions
            return '';
        }

        $s = '';

        /** @var $property PropertyDefinitionInterface */
        foreach ($properties as $property) {
            $this->checkNamespace($property->getName());
            $s .= '- ' . $property->getName();

            if ($property->getRequiredType()) {
                $s .= ' (' . PropertyType::nameFromValue($property->getRequiredType()) . ')';
            }
            $s .= "\n";
            if ($property->getDefaultValues()) {
                $s .= "= '" . implode("', '", $property->getDefaultValues()) . "'\n";
            }
            $attributes = '';
            if ($property->isMandatory()) {
                $attributes .= 'mandatory ';
            }
            if ($property->isAutoCreated()) {
                $attributes .= 'autocreated ';
            }
            if ($property->isProtected()) {
                $attributes .= 'protected ';
            }
            if ($property->isMultiple()) {
                $attributes .= 'multiple ';
            }
            if ($property->getAvailableQueryOperators()) {
                $attributes .= implode("', '", $property->getAvailableQueryOperators());
            }
            if (! $property->isFullTextSearchable()) {
                $attributes .= 'nofulltext ';
            }
            if (! $property->isQueryOrderable()) {
                $attributes .= 'noqueryorder ';
            }

            if (OnParentVersionAction::COPY !== $property->getOnParentVersion()) {
                $attributes .= OnParentVersionAction::nameFromValue($property->getOnParentVersion()) . ' ';
            }

            if ($attributes) {
                $s .= trim($attributes) . "\n";
            }

            if ($property->getValueConstraints()) {
                $s .= "< '" . implode("', '", $property->getValueConstraints()) . "'\n";
            }
        }

        return $s;
    }

    private function writeChildren($children)
    {
        if (null === $children) {
            // getDeclaredChildNodeDefinitions is allowed to return null on
            // newly created node type definitions
            return '';
        }

        $s = '';

        /** @var $child NodeDefinitionInterface */
        foreach ($children as $child) {
            $this->checkNamespace($child->getName());
            $s .= '+ ' . $child->getName();

            if ($child->getRequiredPrimaryTypeNames()) {
                foreach ($child->getRequiredPrimaryTypeNames() as $typeName) {
                    $this->checkNamespace($typeName);
                }
                $s .= ' (' . implode(', ', $child->getRequiredPrimaryTypeNames()) . ')';
            }
            if ($child->getDefaultPrimaryTypeName()) {
                $this->checkNamespace($child->getDefaultPrimaryTypeName());
                $s .= "\n= " . $child->getDefaultPrimaryTypeName();
            }
            $s .= "\n";

            $attributes = '';
            if ($child->isMandatory()) {
                $attributes .= 'mandatory ';
            }
            if ($child->isAutoCreated()) {
                $attributes .= 'autocreated ';
            }
            if ($child->isProtected()) {
                $attributes .= 'protected ';
            }
            if (OnParentVersionAction::COPY != $child->getOnParentVersion()) {
                $attributes .= OnParentVersionAction::nameFromValue($child->getOnParentVersion()) . ' ';
            }
            if ($child->allowsSameNameSiblings()) {
                $attributes .= 'sns ';
            }

            if ($attributes) {
                $s .= trim($attributes) . "\n";
            }
        }

        return $s;
    }
}

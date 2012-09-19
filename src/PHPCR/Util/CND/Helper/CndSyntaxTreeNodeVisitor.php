<?php

namespace PHPCR\Util\CND\Helper;

use PHPCR\Util\CND\Parser\SyntaxTreeNode,
    PHPCR\Util\CND\Parser\SyntaxTreeVisitorInterface,
    PHPCR\NamespaceRegistryInterface,
    PHPCR\NodeType\NodeTypeManagerInterface,
    PHPCR\NodeType\NodeTypeDefinitionInterface,
    PHPCR\PropertyType,
    PHPCR\Version\OnParentVersionAction;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class CndSyntaxTreeNodeVisitor implements SyntaxTreeVisitorInterface
{
    /**
     * @var \PHPCR\NodeType\NodeTypeManagerInterface
     */
    protected $nodeTypeManager;

    /**
     * @var \PHPCR\NamespaceRegistryInterface
     */
    protected $namespaceRegistry;

    /**
     * @var array
     */
    protected $nodeTypeDefs = array();

    /**
     * @var array
     */
    protected $namespaces = array();

    /**
     * @var \PHPCR\NodeType\NodeTypeTemplateInterface
     */
    protected $curNodeTypeDef;

    /**
     * @var \PHPCR\NodeType\PropertyDefinitionTemplateInterface
     */
    protected $curPropTypeDef;

    public function __construct(NamespaceRegistryInterface $nsRegistry, NodeTypeManagerInterface $ntManager)
    {
        $this->namespaceRegistry = $nsRegistry;
        $this->nodeTypeManager = $ntManager;
    }

    public function getNodeTypeDefs()
    {
        return $this->nodeTypeDefs;
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function visit(SyntaxTreeNode $node)
    {
        switch ($node->getType()) {

            case 'nsMapping':
                $this->namespaces[$node->getProperty('prefix')] = $node->getProperty('uri');
                break;

            case 'nodeTypeDef':
                $this->curNodeTypeDef = $this->nodeTypeManager->createNodeTypeTemplate();
                $this->nodeTypeDefs[] = $this->curNodeTypeDef;
                break;

            case 'nodeTypeName':
                if ($node->hasProperty('value')) {
                    $this->curNodeTypeDef->setName($node->getProperty('value'));
                }
                break;

            case 'supertypes':
                if ($node->hasProperty('value')) {
                    $this->curNodeTypeDef->setDeclaredSuperTypeNames($node->getProperty('value'));
                }
                break;

            case 'nodeTypeAttributes':
                $this->setNodeTypeAttributes($node);
                // return false to indicate we don't want to visit the children
                return false;

            case 'propertyDef':
                $this->curPropTypeDef = $this->nodeTypeManager->createPropertyDefinitionTemplate();
                $this->curNodeTypeDef->getPropertyDefinitionTemplates()->append($this->curPropTypeDef);
                break;

            case 'propertyName':
                $this->curPropTypeDef->setName($node->getProperty('value'));
                break;

            case 'propertyType':
                $this->curPropTypeDef->setRequiredType(PropertyType::valueFromName($node->getProperty('value')));
                break;

            case 'defaultValues':
                $this->curPropTypeDef->setDefaultValues($node->getProperty('value'));
                break;

            case 'valueConstraints':
                $this->curPropTypeDef->setValueConstraints($node->getProperty('value'));
                break;

            case 'propertyTypeAttributes':
                $this->setPropertyTypeAttributes($node);
                // return false to indicate we don't want to visit the children
                return false;

            default:
                var_dump(sprintf('Unhandled node [%s]', $node->getType()));

        }
    }

    protected function setNodeTypeAttributes(SyntaxTreeNode $node)
    {
        if ($node->hasChild('orderable')) $this->curNodeTypeDef->setOrderableChildNodes(true);
        if ($node->hasChild('mixin')) $this->curNodeTypeDef->setMixin(true);
        if ($node->hasChild('abstract')) $this->curNodeTypeDef->setAbstract(true);
        if ($node->hasChild('query')) $this->curNodeTypeDef->setQueryable(true);
    }

    protected function setPropertyTypeAttributes(SyntaxTreeNode $node)
    {
        if ($node->hasChild('autocreated')) $this->curPropTypeDef->setAutoCreated(true);
        if ($node->hasChild('mandatory')) $this->curPropTypeDef->setMandatory(true);
        if ($node->hasChild('protected')) $this->curPropTypeDef->setProtected(true);
        if ($node->hasChild('multiple')) $this->curPropTypeDef->setMultiple(true);
        if ($node->hasChild('queryops')) $this->curPropTypeDef->setAvailableQueryOperators($node->getPRoperty('value'));

        if ($node->hasChild('nofulltext')) {
            $this->curPropTypeDef->setFullTextSearchable(false);
        } else {
            // TODO: Check the property should be set to true when there is no nofulltext node
            $this->curPropTypeDef->setFullTextSearchable(true);
        }

        if ($node->hasChild('noqueryorder')) {
            $this->curPropTypeDef->setQueryOrderable(false);
        } else {
            // TODO: Check the property should be set to true when there is no noqueryorder node
            $this->curPropTypeDef->setQueryOrderable(true);
        }

        // WARNING:
        // Potentially the syntax tree could have more than one OPV node, which is wrong in a CND.
        // If this happens the order of the "if" below determine which one will be used.
        if ($node->hasChild('COPY')) $this->curPropTypeDef->setOnParentVersion(OnParentVersionAction::COPY);
        if ($node->hasChild('VERSION')) $this->curPropTypeDef->setOnParentVersion(OnParentVersionAction::VERSION);
        if ($node->hasChild('INITIALIZE')) $this->curPropTypeDef->setOnParentVersion(OnParentVersionAction::INITIALIZE);
        if ($node->hasChild('COMPUTE')) $this->curPropTypeDef->setOnParentVersion(OnParentVersionAction::COMPUTE);
        if ($node->hasChild('IGNORE')) $this->curPropTypeDef->setOnParentVersion(OnParentVersionAction::IGNORE);
        if ($node->hasChild('ABORT')) $this->curPropTypeDef->setOnParentVersion(OnParentVersionAction::ABORT);
        // TODO: What to do with an "OPV" node?
    }

}

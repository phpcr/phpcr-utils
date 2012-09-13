<?php

namespace PHPCR\Util\CND\Helper;

// TODO: this class needs to be rewritten using PHPCR\NodeType\NodeTypeManagerInterface to generate the node types
// TODO: as it is now, this will not work !!!

use PHPCR\Util\CND\Parser\SyntaxTreeNode,
    PHPCR\Util\CND\Parser\SyntaxTreeVisitorInterface,
    PHPCR\NamespaceRegistryInterface,
    PHPCR\NodeType\NodeTypeManagerInterface,
    PHPCR\NodeType\NodeTypeDefinitionInterface;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class CndSyntaxTreeNodeVisitor implements SyntaxTreeVisitorInterface
{
    /**
     * @var \PHPCR\NodeTypeManagerInterface
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
     * @var \PHPCR\NodeTypeTemplateInterface
     */
    protected $curNodeTypeDef;

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
        var_dump($node->getType());

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
                if ($node->hasChild('orderable')) $this->curNodeTypeDef->setOrderableChildNodes(true);
                if ($node->hasChild('mixin')) $this->curNodeTypeDef->setMixin(true);
                if ($node->hasChild('abstract')) $this->curNodeTypeDef->setAbstract(true);
                if ($node->hasChild('query')) $this->curNodeTypeDef->setQueryable(true);
                break;

            // TODO: write the rest
        }
    }

}

<?php

namespace PHPCR\Util\CND\Helper;

// TODO: this class needs to be rewritten using PHPCR\NodeType\NodeTypeManagerInterface to generate the node types
// TODO: as it is now, this will not work !!!

use PHPCR\Util\CND\Parser\SyntaxTreeNode,
    PHPCR\Util\CND\Parser\SyntaxTreeVisitorInterface,
    PHPCR\SessionInterface,
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
     * @var \PHPCR\NodeTypeDefinitionInterface
     */
    protected $curNodeTypeDef;

    public function __construct(SessionInterface $session)
    {
        $this->namespaceRegistry = $session->getWorkspace()->getNamespaceRegistry();
        $this->nodeTypeManager = $session->getWorkspace()->getNodeTypeManager();
    }

    public function getNodeTypeDefs()
    {
        return $this->nodeTypeDefs;
    }

    public function visit(SyntaxTreeNode $node)
    {
        //var_dump($node->getType());
        
        switch ($node->getType()) {

            case 'nodeTypeDef':
                $this->curNodeTypeDef = new NodeTypeDefinition();
                $this->nodeTypeDefs[] = $this->curNodeTypeDef;
                break;
//
//            case 'nodeTypeName':
//                if ($node->hasProperty('value')) {
//                    $this->curNodeTypeDef->setName($node->getProperty('value'));
//                }
//                break;
//
//            case 'supertypes':
//                if ($node->hasProperty('value')) {
//                    $this->curNodeTypeDef->addDeclaredSupertypeName($node->getProperty('value'));
//                }
//                break;
//
//            case 'nodeTypeAttributes':
//                if ($node->hasChild('orderable')) $this->curNodeTypeDef->setHasOrderableChildNodes(true);
//                if ($node->hasChild('mixin')) $this->curNodeTypeDef->setIsMixin(true);
//                if ($node->hasChild('abstract')) $this->curNodeTypeDef->setIsAbstract(true);
//                if ($node->hasChild('query')) $this->curNodeTypeDef->setIsQueryable(true);
//                break;
//
//            // TODO: write the rest
        }
    }

}

<?php

namespace PHPCR\Util\CND\Helper;

use PHPCR\Util\CND\Parser\SyntaxTreeNode,
    PHPCR\WorkspaceInterface;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeTypeGenerator
{
    /**
     * @var \PHPCR\Util\CND\Parser\SyntaxTreeNode
     */
    protected $root;

    /**
     * @var \PHPCR\WorkspaceInterface
     */
    protected $workspace;

    /**
     * @param \PHPCR\WorkspaceInterface $session
     * @param \PHPCR\Util\CND\Parser\SyntaxTreeNode $root
     */
    public function __construct(WorkspaceInterface $workspace, SyntaxTreeNode $root)
    {
        $this->workspace = $workspace;
        $this->root = $root;
    }

    public function generate()
    {
        $visitor = new CndSyntaxTreeNodeVisitor($this->workspace->getNamespaceRegistry(), $this->workspace->getNodeTypeManager());
        $this->root->accept($visitor);
        return array(
            'namespaces' => $visitor->getNamespaces(),
            'nodeTypes' => $visitor->getNodeTypeDefs(),
        );
    }

}

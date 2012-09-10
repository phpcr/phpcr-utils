<?php

namespace PHPCR\Util\CND\Helper;

use PHPCR\Util\CND\Parser\SyntaxTreeNode,
    PHPCR\SessionInterface;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeTypeGenerator
{
    protected $root;

    protected $session;

    /**
     * @param \PHPCR\SessionInterface $session
     * @param \PHPCR\Util\CND\Parser\SyntaxTreeNode $root
     */
    public function __construct(SessionInterface $session, SyntaxTreeNode $root)
    {
        $this->session = $session;
        $this->root = $root;
    }

    public function generate()
    {
        $visitor = new CndSyntaxTreeNodeVisitor($this);
        $this->root->accept($visitor);
        return $visitor->getNodeTypeDefs();
    }

}

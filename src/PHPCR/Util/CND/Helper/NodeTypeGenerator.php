<?php

namespace PHPCR\Util\CND\Helper;

use PHPCR\Util\CND\Parser\SyntaxTreeNode;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeTypeGenerator
{
    protected $root;

    public function __construct(SyntaxTreeNode $root)
    {
        $this->root = $root;
    }

    public function generate()
    {
        $visitor = new CndSyntaxTreeNodeVisitor($this);
        $this->root->accept($visitor);
        return $visitor->getNodeTypeDefs();
    }

}

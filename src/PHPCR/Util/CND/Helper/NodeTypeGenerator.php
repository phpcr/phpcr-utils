<?php

namespace PHPCR\Util\CND\Helper;

use PHPCR\Util\CND\Parser\SyntaxTreeNode;

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

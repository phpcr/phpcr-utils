<?php

namespace PHPCR\Util\CND\Parser;

interface SyntaxTreeVisitorInterface
{
    function visit(SyntaxTreeNode $node);
}
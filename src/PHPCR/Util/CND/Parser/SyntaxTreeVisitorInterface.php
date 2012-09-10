<?php

namespace PHPCR\Util\CND\Parser;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface SyntaxTreeVisitorInterface
{
    function visit(SyntaxTreeNode $node);
}
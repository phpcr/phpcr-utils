<?php

namespace PHPCR\Util\CND\Parser;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
interface ParserInterface
{
    /**
     * @return SyntaxTreeNode The root node of the syntax tree
     */
    function parse();
}

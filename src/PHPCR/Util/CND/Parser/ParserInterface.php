<?php

namespace PHPCR\Util\CND\Parser;

interface ParserInterface
{
    /**
     * @return SyntaxTreeNode The root node of the syntax tree
     */
    function parse();
}

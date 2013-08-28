<?php

namespace PHPCR\Util\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperNodeVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPCR\Util\TreeWalker;
use PHPCR\Util\Console\Helper\TreeDumper\SystemNodeFilter;

/**
 * Helper class to make the session instance available to console commands.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */
class PhpcrConsoleDumperHelper extends Helper
{
    public function getTreeWalker(OutputInterface $output, $options)
    {
        $options = array_merge(array(
            'dump_uuids' => false,
            'ref_format' => 'uuid',
            'show_props' => false,
            'show_sys_nodes' => false,
        ), $options);

        $propVisitor = null;
        $nodeVisitor = new ConsoleDumperNodeVisitor($output, $options['dump_uuids']);

        if (true === $options['show_props']) {
            $propVisitor = new ConsoleDumperPropertyVisitor($output, $options);
        }

        $treeWalker = new TreeWalker($nodeVisitor, $propVisitor);
        if (false === $options['show_sys_nodes']) {
            $filter = new SystemNodeFilter();
            $treeWalker->addNodeFilter($filter);
            $treeWalker->addPropertyFilter($filter);
        }

        return $treeWalker;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'phpcr_console_dumper';
    }
}

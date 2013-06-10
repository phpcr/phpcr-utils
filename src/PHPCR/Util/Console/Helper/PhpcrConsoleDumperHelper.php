<?php

/**
 * This file is part of the PHPCR Utils
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */

namespace PHPCR\Util\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperNodeVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPCR\Util\TreeWalker;
use PHPCR\Util\Console\Helper\TreeDumper\SystemNodeFilter;

/**
 * Helper class to make the session instance available to console commands
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


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

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use PHPCR\ItemNotFoundException;
use PHPCR\RepositoryException;
use PHPCR\PathNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\Util\TreeWalker;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperNodeVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\SystemNodeFilter;

/**
 * Command to dump all nodes under a path to the console
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class DumpCommand extends Command
{
    /**
     * Limit after which to cut lines when dumping properties
     *
     * @var int
     */
    private $dump_max_line_length = 120;

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:dump')
            ->addOption('sys_nodes', null, InputOption::VALUE_NONE, 'Use to dump the system nodes')
            ->addOption('props', null, InputOption::VALUE_NONE, 'Use to dump the node properties')
            ->addOption('depth', null, InputOption::VALUE_OPTIONAL, 'Set to a number to limit how deep into the tree to recurse', "-1")
            ->addOption('identifiers', null, InputOption::VALUE_NONE, 'Use to also output node UUID')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Path of the node to dump', '/')
            ->setDescription('Dump the content repository')
            ->setHelp(<<<EOF
The <info>dump</info> command recursively outputs the name of the node specified
by the <info>identifier</info> argument and its subnodes in a yaml-like style.

If the <info>props</info> option is used the nodes properties are
displayed as yaml arrays.
By default the command filters out system nodes and properties (i.e. nodes and
properties with names starting with 'jcr:'), the <info>sys_nodes</info> option
allows to turn this filter off.
allows to turn this filter off.
EOF
            )
        ;
    }

    /**
     * Change at which length lines in the dump get cut.
     *
     * @param int $length maximum line length after which to cut the output.
     */
    public function setDumpMaxLineLength($length)
    {
        $this->dump_max_line_length = $length;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        // node to dump
        $identifier = $input->getArgument('identifier');

        // whether to dump node uuid
        $identifiers = $input->hasParameterOption('--identifiers');
        $nodeVisitor = new ConsoleDumperNodeVisitor($output, $identifiers);

        $propVisitor = null;
        if ($input->hasParameterOption('--props')) {
            $propVisitor = new ConsoleDumperPropertyVisitor($output);
        }

        $walker = new TreeWalker($nodeVisitor, $propVisitor);

        if (!$input->hasParameterOption('--sys_nodes')) {
            $filter = new SystemNodeFilter();
            $walker->addNodeFilter($filter);
            $walker->addPropertyFilter($filter);
        }

        try {
            if (strpos($identifier, '/') === 0) {
                $node = $session->getNode($identifier);
            } else {
                $node = $session->getNodeByIdentifier($identifier);
            }
            $walker->traverse($node, $input->getOption('depth'));
        } catch (RepositoryException $e) {
            if ($e instanceof PathNotFoundException || $e instanceof ItemNotFoundException) {
                $output->writeln("<error>Path '$identifier' does not exist</error>");
            }
            $output->writeln('<error>Error: '.$e->getMessage().'</error>');

            return 1;
        }

        return 0;
    }

}

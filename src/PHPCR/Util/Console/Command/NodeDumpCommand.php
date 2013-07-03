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

use PHPCR\ItemNotFoundException;
use PHPCR\RepositoryException;
use PHPCR\PathNotFoundException;

use PHPCR\Util\UUIDHelper;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command subtrees under a path to the console
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodeDumpCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:node:dump')
            ->addOption('sys-nodes', null, InputOption::VALUE_NONE, 'Also dump system nodes (recommended to use with a depth limit)')
            ->addOption('props', null, InputOption::VALUE_NONE, 'Also dump properties of the nodes')
            ->addOption('identifiers', null, InputOption::VALUE_NONE, 'Also output node UUID')
            ->addOption('depth', null, InputOption::VALUE_OPTIONAL, 'Limit how many level of children to show', "-1")
            ->addOption('ref-format', 'uuid', InputOption::VALUE_REQUIRED, 'Set the way references should be displayed when dumping reference properties - either "uuid" (default) or "path"')
            ->addArgument('identifier', InputArgument::OPTIONAL, 'Root path to dump', '/')
            ->setDescription('Dump subtrees of the content repository')
            ->setHelp(<<<HERE
The <info>dump</info> command recursively outputs the name of the node specified
by the <info>identifier</info> argument and its subnodes in a yaml-like style.

If the <info>props</info> option is used the nodes properties are
displayed as yaml arrays.

By default the command filters out system nodes and properties (i.e. nodes and
properties with names starting with 'jcr:'), the <info>--sys-nodes</info> option
allows to turn this filter off.
HERE
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getPhpcrSession();
        $dumperHelper = $this->getPhpcrConsoleDumperHelper();

        // node to dump
        $identifier = $input->getArgument('identifier');

        // whether to dump node uuid
        $options = array();
        $options['dump_uuids'] = $input->hasParameterOption('--identifiers');
        $options['ref_format'] = $input->getOption('ref-format');
        $options['show_props'] = $input->hasParameterOption('--props');
        $options['show_sys_nodes'] = $input->hasParameterOption('--sys-nodes');

        if (null !== $options['ref_format']&& !in_array($options['ref_format'], array('uuid', 'path'))) {
            throw new \Exception('The ref-format option must be set to either "path" or "uuid"');
        }

        $walker = $dumperHelper->getTreeWalker($output, $options);

        try {
            if (UUIDHelper::isUUID($identifier)) {
                $node = $session->getNodeByIdentifier($identifier);
            } else {
                $node = $session->getNode($identifier);
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

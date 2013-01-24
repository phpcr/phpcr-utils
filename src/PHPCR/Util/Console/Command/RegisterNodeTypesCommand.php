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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\SessionInterface;
use PHPCR\NodeType\NodeTypeExistsException;

/**
 * Command to load and register a node type defined in a common nodetype
 * definition (CND) file.
 *
 * See the link below for the cnd definition.
 * @link http://jackrabbit.apache.org/node-type-notation.html
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class RegisterNodeTypesCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:register-node-types')
            ->setDescription('Register node types in the PHPCR repository')
            ->setDefinition(array(
                new InputArgument(
                    'cnd-file', InputArgument::REQUIRED, 'Register namespaces and node types from a "Compact Node Type Definition" .cnd file'
                ),
                new InputOption('allow-update', '', InputOption::VALUE_NONE, 'Overwrite existig node type'),
            ))
            ->setHelp(<<<EOT
Register node types in the PHPCR repository.

This command allows to register node types in the repository that are defined
in a CND (Compact Namespace and Node Type Definition) file as used by jackrabbit.

Custom node types can be used to define the structure of content repository
nodes, like allowed properties and child nodes together with the namespaces
and their prefix used for the names of node types and properties.

If you use --allow-update existing node type definitions will be overwritten
in the repository.
EOT
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cnd_file = realpath($input->getArgument('cnd-file'));

        if (!file_exists($cnd_file)) {
            throw new \InvalidArgumentException(
                sprintf("Node type definition file '<info>%s</info>' does not exist.", $cnd_file)
            );
        } elseif (!is_readable($cnd_file)) {
            throw new \InvalidArgumentException(
                sprintf("Node type definition file '<info>%s</info>' does not have read permissions.", $cnd_file)
            );
        }

        $cnd = file_get_contents($cnd_file);
        $allowUpdate = $input->getOption('allow-update');
        $session = $this->getHelper('phpcr')->getSession();

        try {
            $this->updateFromCnd($output, $session, $cnd, $allowUpdate);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->write(PHP_EOL.sprintf('Successfully registered node types from "<info>%s</info>"', $cnd_file) . PHP_EOL);

        return 0;
    }

    /**
     * Actually do the update.
     *
     * @param OutputInterface  $output      the console output stream
     * @param SessionInterface $session     the PHPCR session to talk to
     * @param string           $cnd         the compact namespace and node type definition in string form
     * @param bool             $allowUpdate whether to allow updating existing node types.
     *
     * @throws \PHPCR\RepositoryException on other errors
     */
    protected function updateFromCnd(OutputInterface $output, SessionInterface $session, $cnd, $allowUpdate)
    {
        $ntm = $session->getWorkspace()->getNodeTypeManager();

        try {
            $ntm->registerNodeTypesCnd($cnd, $allowUpdate);
        } catch (NodeTypeExistsException $e) {
            if (!$allowUpdate) {
                $output->write(PHP_EOL.'<error>The node type(s) you tried to register already exist.</error>'.PHP_EOL);
                $output->write(PHP_EOL.'If you want to override the existing definition call this command with the ');
                $output->write('<info>--allow-update</info> option.'.PHP_EOL);
            }
            throw $e;
        }
    }
}

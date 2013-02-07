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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

use PHPCR\Util\NodeHelper;

/**
 * Command to remove all nodes from a path in the workspace of the configured
 * session.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class PurgeCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:purge')
            ->setDescription('Remove content from the repository')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path of the node to purge', '/')
            ->addOption('force', null, InputOption::VALUE_OPTIONAL, 'Set to "yes" to bypass the confirmation dialog', "no")
            ->setHelp(<<<EOF
The <info>phpcr:purge</info> command remove all the non-standard nodes from the content repository
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $path = $input->getArgument('path');
        $force = $input->hasParameterOption('--force');

        if (! $force) {
            $dialog = new DialogHelper();
            $workspaceName = $session->getWorkspace()->getName();
            $force = $dialog->askConfirmation($output, "Are you sure you want to purge path '$path' and all its children from the workspace '$workspaceName'? [yes|no]: ", false);
        }

        if ($force) {
            if ('/' === $path) {
                NodeHelper::deleteAllNodes($this->getHelper('phpcr')->getSession());
            } else {
                $session->removeItem($path);
            }

            $session->save();
            $output->writeln("Done purging '$path' and all its children\n");
        } else {
            $output->writeln("Aborted\n");
        }

        return 0;
    }
}

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
 * @author Daniel Leech <daniel@dantleech.com>
 */
class WorkspacePurgeCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:workspace:purge')
            ->setDescription('Remove all nodes from a workspace')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Use to bypass the confirmation dialog')
            ->setHelp(<<<EOF
The <info>phpcr:purge</info> command remove all the non-standard nodes from the workspace.
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
        $force = $input->getOption('force');

        if (!$force) {
            $dialog = new DialogHelper();
            $workspaceName = $session->getWorkspace()->getName();
            $force = $dialog->askConfirmation($output, sprintf(
                '<question>Are you sure you want to purge workspace "%s" Y/N ?</question>', 
                $workspaceName
            ), false);
        }

        if ($force) {
            $message = '';
            $output->writeln(sprintf('<info>Purging workspace:</info> %s', $path));
            NodeHelper::purgeWorkspace($session);
            $session->save();
        } else {
            $output->writeln('<error>Aborted</error>');
        }

        return 0;
    }
}


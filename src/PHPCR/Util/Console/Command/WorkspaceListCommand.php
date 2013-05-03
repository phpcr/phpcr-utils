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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to list all workspaces visible through the current session
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ListWorkspacesCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:workspace:list')
            ->setDescription('List all available workspaces in the configured repository')
            ->setHelp(<<<EOT
The <info>workspace:list</info> command lists all avaialable workspaces.
EOT
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $workspaces = $session->getWorkspace()->getAccessibleWorkspaceNames();

        $output->writeln("The following ".count($workspaces)." workspaces are available:");
        foreach ($workspaces as $workspace) {
            $output->writeln($workspace);
        }

        return 0;
    }
}

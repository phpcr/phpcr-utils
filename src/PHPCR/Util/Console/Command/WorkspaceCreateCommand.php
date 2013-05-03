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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to create a workspace in the PHPCR repository
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author David Buchmann <david@liip.ch>
 */
class WorkspaceCreateCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:workspace:create')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the workspace to create')
            ->setDescription('Create a workspace in the configured repository')
            ->setHelp(<<<EOT
The <info>workspace:create</info> command creates a workspace with the specified name.
It will fail if a workspace with that name already exists or if the repository implementation
does not support this operation.
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

        $name = $input->getArgument('name');

        $workspace = $session->getWorkspace();

        if (! $session->getRepository()->getDescriptor(\PHPCR\RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)) {
            $output->writeln('<error>Your PHPCR implementation does not support workspace management. Please refer to the documentation of your PHPCR implementation to learn how to create a workspace.</error>');

            return 1;
        }

        $workspace->createWorkspace($name);

        $output->writeln("Created workspace '$name'.");

        return 0;
    }
}

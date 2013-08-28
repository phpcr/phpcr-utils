<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\RepositoryInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to create a workspace in the PHPCR repository
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
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
does not support the workspace creation operation.
EOT
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $session SessionInterface */
        $session = $this->getHelper('phpcr')->getSession();

        $workspaceName = $input->getArgument('name');

        $workspace = $session->getWorkspace();
        $repo = $session->getRepository();

        if (!$repo->getDescriptor(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)) {
            $output->writeln(
                '<error>Your PHPCR implementation does not support '.
                'workspace management. Please refer to the documentation '.
                'of your PHPCR implementation to learn how to create a workspace.</error>'
            );

            return 1;
        }

        $workspace->createWorkspace($workspaceName);

        $output->writeln("Created workspace '$workspaceName'.");

        return 0;
    }
}

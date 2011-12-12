<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to create a workspace in the phcpr repository
 *
 * @author Lukas
 * @author David Buchmann <david@liip.ch>
 */
class CreateWorkspaceCommand extends Command
{
    /**
     * @see Command
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
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $name = $input->getArgument('name');

        $workspace = $session->getWorkspace();

        if (! $session->getRepository()->getDescriptor(\PHPCR\RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)) {
            throw new \Exception('Your PHPCR implemenation does not support workspace management. Please refer to the documentation of your PHPCR implementation to learn how to create a workspace.');
        }

        $workspace->createWorkspace($name);

        $output->writeln("Created workspace '$name'.");

        return 0;
    }
}

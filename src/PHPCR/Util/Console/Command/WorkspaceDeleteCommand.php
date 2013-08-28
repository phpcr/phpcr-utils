<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\RepositoryInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to delete a workspace in the PHPCR repository
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class WorkspaceDeleteCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:workspace:delete')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the workspace to delete')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Use to bypass the confirmation dialog')
            ->setDescription('Delete a workspace from the configured repository')
            ->setHelp(<<<EOT
The <info>workspace:delete</info> command deletes the workspace with the specified name if it
exists. If the workspace with that name does not yet exist, the command will not fail.
However, if the workspace does exist but the repository implementation does not support
the delete operation, the command will fail.
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

        if (! in_array($workspaceName, $workspace->getAccessibleWorkspaceNames())) {
            $output->writeln("Workspace '$workspaceName' does not exist.");

            return 0;
        }

        if (!$repo->getDescriptor(RepositoryInterface::OPTION_WORKSPACE_MANAGEMENT_SUPPORTED)) {
            $output->writeln(
                '<error>Your PHPCR implementation does not support '.
                'workspace management. Please refer to the documentation '.
                'of your PHPCR implementation to learn how to remove a workspace.</error>'
            );

            return 1;
        }

        $force = $input->getOption('force');
        if (!$force) {
            $dialog = new DialogHelper();
            $force = $dialog->askConfirmation($output, sprintf(
                '<question>Are you sure you want to delete workspace "%s" Y/N ?</question>',
                $workspaceName
            ), false);
        }
        if (!$force) {
            $output->writeln('<error>Aborted</error>');

            return 1;
        }

        $workspace->deleteWorkspace($workspaceName);

        $output->writeln("Deleted workspace '$workspaceName'.");

        return 0;
    }
}

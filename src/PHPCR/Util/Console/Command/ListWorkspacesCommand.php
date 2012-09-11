<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to create a workspace in the phcpr repository
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ListWorkspacesCommand extends Command
{
    /**
     * @see Command
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
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $workspaces = $session->getWorkspace()->getAccessibleWorkspaceNames();

        $output->writeln("The following ".count($workspaces)." workspaces are available.");
        foreach ($workspaces as $workspace) {
            $output->writeln($workspace);
        }

        return 0;
    }
}

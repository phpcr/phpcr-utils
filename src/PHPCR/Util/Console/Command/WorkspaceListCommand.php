<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to list all workspaces visible through the current session
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class WorkspaceListCommand extends Command
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

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
            ->setHelp(<<<EOT
Create a workspace for the given name.
EOT
        );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $name = $input->getArgument('name');

        $session->getWorkspace()->createWorkspace($name);

        $output->writeln("Created workspace '$name'.");
    }
}

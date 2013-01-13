<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

use PHPCR\Util\NodeHelper;
use PHPCR\Util\Console\Helper\ConsoleParametersParser;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class PurgeCommand extends Command
{
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
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $path = $input->getArgument('path');
        $force = ConsoleParametersParser::isTrueString($input->getOption('force'));

        if (! $force) {
            $dialog = new DialogHelper();
            $workspaceName = $session->getWorkspace()->getName();
            $force = $dialog->askConfirmation($output, "Are you sure you want to purge path '$path' and all its children from the workspace '$workspaceName'? [yes|no]: ", false);
        }

        if ($force) {
            if ('/' === $path) {
                NodeHelper::purgeWorkspace($this->getHelper('phpcr')->getSession());
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

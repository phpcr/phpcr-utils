<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
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
            ->setDescription('Remove all content from the repository')
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

        $force = ConsoleParametersParser::isTrueString($input->getOption('force'));

        if (! $force) {
            $dialog = new DialogHelper();
            $res = $dialog->askConfirmation($output, 'Are you sure you want to delete all the nodes of the content repository? [yes|no]: ', false); // TODO: output server and workspace name
        }

        if ($force || $res) {
            NodeHelper::deleteAllNodes($this->getHelper('phpcr')->getSession());
            $session->save();
            $output->writeln("Done\n");
        } else {
            $output->writeln("Aborted\n");
        }

        return 0;
    }
}

<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\Util\NodeHelper;
use PHPCR\Util\TreeWalker;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperNodeVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\SystemNodeFilter;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class Sql2Command extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcr:sql2')
            ->addArgument('query', InputArgument::REQUIRED, 'JCR SQL2 statement to execute')
            ->setDescription('Execute a JCR SQL2 statement')
            ->setHelp("The <info>sql2</info> command executes a JCR SQL2 statement on the content repository");
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
        $sql = $input->getArgument('query');

        $session = $this->getHelper('phpcr')->getSession();
        $qm = $session->getWorkspace()->getQueryManager();
        $query = $qm->createQuery($sql, \PHPCR\Query\QueryInterface::JCR_SQL2);

        $result = $query->execute();
        foreach ($result as $i => $row) {
            $output->writeln("\n".($i+1).'. Row (Path: '. $row->getPath() .', Score: '. $row->getScore() .'):');
            foreach ($row as $column => $value) {
                $output->writeln("$column: $value");
            }
        }

        return 0;
    }
}

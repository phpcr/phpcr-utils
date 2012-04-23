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
class QueryCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcr:query')
            ->addArgument('query', InputArgument::REQUIRED, 'A query statement to execute')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'The query language (sql, jcr_sql2', 'jcr_sql2')
            ->setDescription('Execute a JCR SQL2 statement')
            ->setHelp("The <info>query</info> command executes a JCR query statement on the content repository");
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
        $language = strtoupper($input->getOption('language'));
        if (!defined('\PHPCR\Query\QueryInterface::'.$language)) {
            throw new \RuntimeException("Query language '\\PHPCR\\Query\\QueryInterface::$language' not defined.");
        }

        $query = $qm->createQuery($sql, constant('\PHPCR\Query\QueryInterface::'.$language));

        $result = $query->execute();
        $output->writeln("Results:\n");
        foreach ($result as $i => $row) {
            $output->writeln("\n".($i+1).'. Row (Path: '. $row->getPath() .', Score: '. $row->getScore() .'):');
            foreach ($row as $column => $value) {
                $output->writeln("$column: $value");
            }
        }

        return 0;
    }
}

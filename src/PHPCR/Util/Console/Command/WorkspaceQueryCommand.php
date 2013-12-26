<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to run a PHPCR query from the command line and dump the
 * resulting nodes.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class WorkspaceQueryCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcr:workspace:query')
            ->addArgument('query', InputArgument::REQUIRED, 'A query statement to execute')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'The query language (e.g. jcr-sql2', 'jcr-sql2')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'The query limit', 0)
            ->addOption('offset', null, InputOption::VALUE_OPTIONAL, 'The query offset', 0)
            ->setDescription('Execute a JCR SQL2 statement')
            ->setHelp("The <info>query</info> command executes a JCR query statement on the content repository");
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = $input->getArgument('query');
        $language = strtoupper($input->getOption('language'));
        $limit = $input->getOption('limit');
        $offset = $input->getOption('offset');

        $helper = $this->getPhpcrCliHelper();
        $session = $this->getPhpcrSession();

        $query = $helper->createQuery($language, $sql);

        if ($limit) {
            $query->setLimit($limit);
        }

        if ($offset) {
            $query->setOffset($offset);
        }

        $output->writeln(sprintf('<info>Executing, language:</info> %s', $query->getLanguage()));

        $start = microtime(true);
        $result = $query->execute();
        $elapsed = microtime(true) - $start;

        $output->writeln("Results:\n");
        foreach ($result as $i => $row) {
            $output->writeln("\n".($i+1).'. Row (Path: '. $row->getPath() .', Score: '. $row->getScore() .'):');
            foreach ($row as $column => $value) {
                $output->writeln("$column: ".var_export($value, true));
            }
        }
        $output->writeln(sprintf('<info>%.2f seconds</info>', $elapsed));

        return 0;
    }
}

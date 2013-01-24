<?php

/**
 * This file is part of the PHPCR Utils
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */

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
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class QueryCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcr:query')
            ->addArgument('query', InputArgument::REQUIRED, 'A query statement to execute')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'The query language (sql, jcr_sql2', 'jcr_sql2')
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

        $session = $this->getHelper('phpcr')->getSession();
        $qm = $session->getWorkspace()->getQueryManager();
        $language = strtoupper($input->getOption('language'));
        $limit = $input->getOption('limit');
        $offset = $input->getOption('offset');
        if (!defined('\PHPCR\Query\QueryInterface::'.$language)) {
            throw new \RuntimeException("Query language '\\PHPCR\\Query\\QueryInterface::$language' not defined.");
        }

        $query = $qm->createQuery($sql, constant('\PHPCR\Query\QueryInterface::'.$language));
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
                $output->writeln("$column: $value");
            }
        }
        $output->writeln(sprintf('<info>%.2f seconds</info>', $elapsed));

        return 0;
    }
}

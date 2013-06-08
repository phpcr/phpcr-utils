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
use Symfony\Component\Console\Helper\DialogHelper;

/**
 * Command which can update the properties of nodes found
 * using the given JCR query.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class WorkspaceNodeUpdateCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcr:workspace:node:update')
            ->addArgument(
                'query', 
                InputArgument::REQUIRED, 
                'A query statement to execute')
            ->addOption('force', null, 
                InputOption::VALUE_NONE, 
                'Use to bypass the confirmation dialog'
            )
            ->addOption(
                'language', 'l', 
                InputOption::VALUE_OPTIONAL, 
                'The query language (sql, jcr_sql2')

            ->addOption('set-prop', 'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Set node property on nodes use foo=bar'
            )
            ->addOption('remove-prop', 'r',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Remove property from nodes'
            )
            ->addOption('add-mixin', null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Add a mixin to the nodes'
            )
            ->addOption('remove-mixin', null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Remove mixin from the nodes'
            )
            ->setDescription('Command to manipulate the nodes in the workspace.')
            ->setHelp(<<<HERE
The <info>workspace:node:update</info> command updates properties of nodes found
by the given JCR query.

    php bin/phpcr workspace:node:update "SELECT FROM nt:unstructured" --set-prop=foo=bar

The options for manipulating nodes are the same as with the 
<info>node:touch</info> command and
can be repeated to update multiple properties.
HERE
);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sql = $input->getArgument('query');
        $language = strtoupper($input->getOption('language'));
        $setProp = $input->getOption('set-prop');
        $removeProp = $input->getOption('remove-prop');
        $addMixins = $input->getOption('add-mixin');
        $removeMixins = $input->getOption('remove-mixin');
        $force = $input->getOption('force');

        $helper = $this->getHelper('phpcr');
        $session = $helper->getSession();

        $query = $helper->createQuery($language, $sql);

        $start = microtime(true);
        $result = $query->execute();
        $elapsed = microtime(true) - $start;

        if (!$force) {
            $dialog = new DialogHelper();
            $force = $dialog->askConfirmation($output, sprintf(
                '<question>About to update %d nodes, do you want to continue Y/N ?</question>',
                count($result)
            ), false);
        }

        foreach ($result as $i => $row) {
            $output->writeln(sprintf(
                "<info>Updating node</info> %s.",
                $row->getPath()
            ));

            $node = $row->getNode();

            $helper->processNode($output, $node, array(
                'setProp' => $setProp,
                'removeProp' => $removeProp,
                'addMixins' => $addMixins,
                'removeMixins' => $removeMixins,
            ));
        }

        $output->writeln(sprintf('<info>%.2f seconds</info>', $elapsed));

        return 0;
    }
}

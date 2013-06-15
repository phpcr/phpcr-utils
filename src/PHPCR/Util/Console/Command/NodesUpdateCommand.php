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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use PHPCR\Util\Console\Command\BaseCommand;

/**
 * Command which can update the properties of nodes found
 * using the given JCR query.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodesUpdateCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->configureNodeManipulationInput();

        $this->setName('phpcr:nodes:update')
            ->addOption(
                'query', null,
                InputOption::VALUE_REQUIRED,
                'Query used to select the nodes'
            )
            ->addOption(
                'query-language', 'l', 
                InputOption::VALUE_OPTIONAL, 
                'The query language (e.g. sql, jcr_sql2)',
                'jcr-sql2'
            )
            ->setDescription('Command to manipulate the nodes in the workspace.')
            ->setHelp(<<<HERE
The <info>phpcr:nodes:update</info> can manipulate the properties of nodes 
found using the given query.

For example, to set the property "foo" to "bar" on all unstructured nodes:

    php bin/phpcr phpcr:nodes:update --query="SELECT * FROM [nt:unstructured]" --set-prop=foo=bar

Or to update only nodes matching a certain criteria:

    php bin/phpcr nodes:update --query="SELECT * FROM [nt:unstructured] WHERE [phpcr:class]=\"Some\\Class\\Here\" --add-mixin=mix:mimetype

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
        $this->dialog = new DialogHelper();

        $query = $input->getOption('query');
        $queryLanguage = strtoupper($input->getOption('query-language'));
        $setProp = $input->getOption('set-prop');
        $removeProp = $input->getOption('remove-prop');
        $addMixins = $input->getOption('add-mixin');
        $removeMixins = $input->getOption('remove-mixin');
        $applyClosures = $input->getOption('apply-closure');
        $noInteraction = $input->getOption('no-interaction');
        $helper = $this->getPhpcrCliHelper();
        $session = $this->getPhpcrSession();

        if (!$query) {
            throw new \InvalidArgumentException(
                'You must provide a SELECT query, e.g. --select="SELECT * FROM [nt:unstructured]"'
            );
        }

        if (strtoupper(substr($query, 0, 6) != 'SELECT')) {
            throw new \InvalidArgumentException(sprintf(
                'Query doesn\'t look like a SELECT query: "%s"',
                $query
            ));
        }

        $query = $helper->createQuery($queryLanguage, $query);
        $result = $query->execute();

        if (!$noInteraction) {
            if (false === $this->getAction($output, $result)) {
                return 0;
            }
        }

        foreach ($result as $i => $row) {
            $output->writeln(sprintf(
                "<info>Updating node:</info> [%d] %s.",
                $i,
                $row->getPath()
            ));

            $node = $row->getNode();

            $helper->processNode($output, $node, array(
                'setProp' => $setProp,
                'removeProp' => $removeProp,
                'addMixins' => $addMixins,
                'removeMixins' => $removeMixins,
                'applyClosures' => $applyClosures,
            ));
        }

        $output->writeln('<info>Saving session...</info>');
        $session->save();
        $output->writeln('<info>Done.</info>');

        return 0;
    }

    protected function getAction($output, $result)
    {
        $response = strtoupper($this->dialog->ask($output, sprintf(
            '<question>About to update %d nodes. Enter "Y" to continue, "N" to cancel or "L" to list.</question>',
            count($result->getRows())
        ), false));

        if ($response == 'L') {
            foreach ($result as $i => $row) {
                $output->writeln(sprintf(' - [%d] %s', $i, $row->getPath()));
            }

            return $this->getAction($output, $result);
        }

        if ($response == 'N') {
            return false;
        }

        if ($response == 'Y') {
            return true;
        }

        return $this->getAction($output, $result);
    }
}

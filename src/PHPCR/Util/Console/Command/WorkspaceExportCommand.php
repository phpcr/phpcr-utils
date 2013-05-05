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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\RepositoryInterface;

/**
 * Command to export a workspace to an XML file.
 *
 * @author David Buchmann <david@liip.ch>
 */
class WorkspaceExportCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:workspace:export')
            ->addArgument('filename', InputArgument::REQUIRED, 'The xml file to export to')
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Path of the node to export', '/')
            ->addOption('skip_binary', null, InputOption::VALUE_OPTIONAL, 'Set to "yes" to skip binaries', "no")
            ->addOption('recurse', null, InputOption::VALUE_OPTIONAL, 'Set to "no" to prevent recursion', "yes")
            ->setDescription('Export nodes from the repository, either to the JCR system view format or the document view format')
            ->setHelp(<<<EOF
The <info>export</info> command uses the PHPCR SessionInterface::exportSystemView
method to export parts of the repository into an XML document.

If the <info>path</info> option is set, given path is exported.
Otherwise the entire repository is exported.
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        if (!$session->getRepository()->getDescriptor(RepositoryInterface::OPTION_XML_EXPORT_SUPPORTED)) {
            $output->writeln('<error>This repository does not support xml export</error>');

            return 1;
        }

        $path = $input->getOption('path');
        $stream = fopen($input->getArgument('filename'), 'w');
        $session->exportSystemView($path, $stream, $input->getOption('skip_binary') === 'yes', $input->getOption('recurse') === 'no');

        return 0;
    }
}

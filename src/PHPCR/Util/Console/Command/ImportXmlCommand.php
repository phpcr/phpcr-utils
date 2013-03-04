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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\RepositoryInterface;
use PHPCR\ImportUUIDBehaviorInterface;

/**
 * Command to import a system or document view XML into the repository.
 *
 * @author David Buchmann <david@liip.ch>
 */
class ImportXmlCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:import')
            ->addArgument('filename', null, 'The xml file to import')
            ->addOption('parentpath', 'p', InputOption::VALUE_OPTIONAL, 'Repository path to the parent where to import the file contents', '/')
            ->setDescription('Import xml data into the repository, either in JCR system view format or arbitrary xml')
            ->setHelp(<<<EOF
The <info>import</info> command uses the PHPCR SessionInterface::importXml method
to import an XML document into the repository. If the document is in the JCR
system view format, it is interpreted according to the spec, otherwise it is
treated as document view format, meaning XML elements are translated to nodes
and XML attributes into properties.

If the <info>parentpath</info> option is set, the document is imported to that
path. Otherwise the document is imported at the repository root.
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $session \PHPCR\SessionInterface */
        $session = $this->getHelper('phpcr')->getSession();
        if (! $session->getRepository()->getDescriptor(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)) {
            $output->writeln('<error>This repository does not support xml import</error>');

            return 1;
        }

        $parentpath = $input->getOption('parentpath');
        $session->importXml($parentpath, $input->getArgument('filename'), ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW);
        $session->save();

        return 0;
    }
}

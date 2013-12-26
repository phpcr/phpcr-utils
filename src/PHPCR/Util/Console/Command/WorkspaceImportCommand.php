<?php

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
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author David Buchmann <david@liip.ch>
 */
class WorkspaceImportCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:workspace:import')
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
        $filename = $input->getArgument('filename');
        $parentPath = $input->getOption('parentpath');
        $session = $this->getHelper('phpcr')->getSession();
        $repo = $session->getRepository();

        if (!$repo->getDescriptor(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)) {
            $output->writeln('<error>This repository does not support xml import</error>');

            return 1;
        }

        $session->importXml(
            $parentPath,
            $filename,
            ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW
        );
        $session->save();

        return 0;
    }
}

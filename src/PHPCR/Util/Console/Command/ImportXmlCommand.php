<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\RepositoryInterface;
use PHPCR\ImportUUIDBehaviorInterface;

class ImportXmlCommand extends Command
{
    /**
     * @see Command
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
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $session \PHPCR\SessionInterface */
        $session = $this->getHelper('phpcr')->getSession();
        if (! $session->getRepository()->getDescriptor(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)) {
            throw new \RuntimeException('This repository does not support xml import');
        }

        $parentpath = $input->getOption('parentpath');
        $session->importXml($parentpath, $input->getArgument('filename'), ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW);
        $session->save();

        return 0;
    }
}

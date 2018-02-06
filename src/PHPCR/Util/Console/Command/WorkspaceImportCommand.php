<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\ImportUUIDBehaviorInterface;
use PHPCR\RepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to import a system or document view XML into the repository.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author David Buchmann <mail@davidbu.ch>
 */
class WorkspaceImportCommand extends BaseCommand
{
    const UUID_BEHAVIOR = [
        'new'     => ImportUUIDBehaviorInterface::IMPORT_UUID_CREATE_NEW,
        'remove'  => ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REMOVE_EXISTING,
        'replace' => ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_REPLACE_EXISTING,
        'throw'   => ImportUUIDBehaviorInterface::IMPORT_UUID_COLLISION_THROW,
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:workspace:import')
            ->addArgument('filename', null, 'The xml file to import')
            ->addOption('parentpath', 'p', InputOption::VALUE_OPTIONAL, 'Repository path to the parent where to import the file contents', '/')
            ->addOption('uuid-behavior', null, InputOption::VALUE_REQUIRED, 'How to handle UUID collisions during the import', 'new')
            ->setDescription('Import xml data into the repository, either in JCR system view format or arbitrary xml')
            ->setHelp(<<<'EOF'
The <info>import</info> command uses the PHPCR SessionInterface::importXml method
to import an XML document into the repository. If the document is in the JCR
system view format, it is interpreted according to the spec, otherwise it is
treated as document view format, meaning XML elements are translated to nodes
and XML attributes into properties.

If the <info>parentpath</info> option is set, the document is imported to that
path. Otherwise the document is imported at the repository root.

The optional <info>uuid-behavior</info> option describes how UUIDs should be
handled. The following options are available:

* <info>new</info> recreate a new uuid for each imported node;
* <info>remove</info> on collision, remove the old node from the repository and
  put the imported data in the tree;
* <info>replace</info> on collision, replace the existing node with the one being
  imported. All children of the imported node also go to the new path;
* <info>throw</info> throw an exception on uuid collision.

EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $parentPath = $input->getOption('parentpath');
        $session = $this->getPhpcrSession();
        $repo = $session->getRepository();

        if (!$repo->getDescriptor(RepositoryInterface::OPTION_XML_IMPORT_SUPPORTED)) {
            $output->writeln('<error>This repository does not support xml import</error>');

            return 1;
        }

        $uuidBehavior = $input->getOption('uuid-behavior');
        if (!array_key_exists($uuidBehavior, self::UUID_BEHAVIOR)) {
            $output->writeln(sprintf('<error>UUID-Behavior "%s" is not supported</error>', $uuidBehavior));
            $output->writeln(sprintf('Supported behaviors are %s', implode(', ', array_keys(self::UUID_BEHAVIOR))));

            return 1;
        }

        $session->importXML($parentPath, $filename, self::UUID_BEHAVIOR[$uuidBehavior]);
        $session->save();

        $output->writeln(sprintf(
            '<info>Successfully imported file "%s" to path "%s" in workspace "%s".</info>',
            realpath($filename),
            $parentPath,
            $session->getWorkspace()->getName()
        ));

        return 0;
    }
}

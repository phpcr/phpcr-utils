<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\SessionInterface;
use PHPCR\NodeType\NodeTypeExistsException;

/**
 * Command to load and register a node type defined in a common nodetype
 * definition (CND) file.
 *
 * See the link below for the cnd definition.
 * @link http://jackrabbit.apache.org/node-type-notation.html
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodeTypeRegisterCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:node-type:register')
            ->setDescription('Register node types in the PHPCR repository')
            ->addArgument('cnd-file', InputArgument::IS_ARRAY, 'Register namespaces and node types from a "Compact Node Type Definition" .cnd file(s)')
            ->addOption('allow-update', null, InputOption::VALUE_NONE, 'Overwrite existig node type')
            ->setHelp(<<<EOT
Register node types in the PHPCR repository.

This command allows to register node types in the repository that are defined
in a CND (Compact Namespace and Node Type Definition) file as defined in the JCR-283
specification.

Custom node types can be used to define the structure of content repository
nodes, like allowed properties and child nodes together with the namespaces
and their prefix used for the names of node types and properties.

This command allows you to specify multiple files and/or folders:

    $ php app/console phpcr:node-type:register /path/to/nodetype1.cnd /path/to/a/folder

When a folder is specified all files within the folder that have the <comment>.cnd</comment>
extension will be treated as node definition files.

If you use <info>--allow-update</info> existing node type definitions will be overwritten
in the repository.
EOT
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $definitions = $input->getArgument('cnd-file');

        if (count($definitions) == 0) {
            throw new \InvalidArgumentException(
                'At least one definition (i.e. file or folder) must be specified'
            );
        }

        $allowUpdate = $input->getOption('allow-update');
        $session = $this->getPhpcrSession();

        $filePaths = $this->getFilePaths($definitions);

        $count = 0;
        foreach ($filePaths as $filePath) {
            $cnd = file_get_contents($filePath);
            $this->updateFromCnd($output, $session, $cnd, $allowUpdate);
            $output->writeln(sprintf('Node type definition: <info>%s</info>', $filePath));
            $count++;
        }

        $output->writeln(sprintf('%d node definition(s) registered', $count));

        return 0;
    }

    /**
     * Actually do the update.
     *
     * @param OutputInterface  $output      the console output stream
     * @param SessionInterface $session     the PHPCR session to talk to
     * @param string           $cnd         the compact namespace and node type definition in string form
     * @param bool             $allowUpdate whether to allow updating existing node types.
     *
     * @throws \PHPCR\RepositoryException on other errors
     */
    protected function updateFromCnd(OutputInterface $output, SessionInterface $session, $cnd, $allowUpdate)
    {
        $ntm = $session->getWorkspace()->getNodeTypeManager();

        try {
            $ntm->registerNodeTypesCnd($cnd, $allowUpdate);
        } catch (NodeTypeExistsException $e) {
            if (!$allowUpdate) {
                $output->write(PHP_EOL.'<error>The node type(s) you tried to register already exist.</error>'.PHP_EOL);
                $output->write(PHP_EOL.'If you want to override the existing definition call this command with the ');
                $output->write('<info>--allow-update</info> option.'.PHP_EOL);
            }
            throw $e;
        }
    }

    /**
     * Return a list of node type definition file paths from
     * the given definition files or folders.
     *
     * @param array $definitions List of files of folders
     *
     * @return array Array of full paths to all the type node definition files.
     */
    protected function getFilePaths($definitions)
    {
        $filePaths = array();

        foreach ($definitions as $definition) {

            if (is_dir($definition)) {
                $dirHandle = opendir($definition);

                while ($file = readdir($dirHandle)) {
                    if (false === $this->fileIsNodeType($file)) {
                        continue;
                    }

                    $filePath = sprintf('%s/%s', $definition, $file);

                    if (!is_readable($filePath)) {
                        throw new \InvalidArgumentException(
                            sprintf("Node type definition file '<info>%s</info>' does not have read permissions.", $file)
                        );
                    }

                    $filePaths[] = $filePath;
                }
            } else {
                if (!file_exists($definition)) {
                    throw new \InvalidArgumentException(
                        sprintf("Node type definition file / folder '<info>%s</info>' does not exist.", $definition)
                    );
                }

                $filePaths[] = $definition;
            }
        }

        return $filePaths;
    }

    protected function fileIsNodeType($filename)
    {
        if (substr($filename, -4) == '.cnd') {
            return true;
        }

        return false;
    }
}

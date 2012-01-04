<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\SessionInterface;

/**
 * Command to load and register a node type defined in a CND file.
 *
 * See the link below for the cnd definition.
 * @link http://jackrabbit.apache.org/node-type-notation.html
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class RegisterNodeTypesCommand extends Command
{
   /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:register-node-types')
            ->setDescription('Register node types in the PHPCR repository')
            ->setDefinition(array(
                new InputArgument(
                    'cnd-file', InputArgument::REQUIRED, 'Register namespaces and node types from a "Compact Node Type Definition" .cnd file'
                ),
                new InputOption('allow-update', '', InputOption::VALUE_NONE, 'Overwrite existig node type'),
            ))
            ->setHelp(<<<EOT
Register node types in the PHPCR repository.

This command allows to register node types in the repository that are defined
in a CND (Compact Namespace and Node Type Definition) file as used by jackrabbit.

Custom node types can be used to define the structure of content repository
nodes, like allowed properties and child nodes together with the namespaces
and their prefix used for the names of node types and properties.

If you use --allow-update existing node type definitions will be overwritten
in the repository.
EOT
            )
        ;
    }

   /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cnd_file = realpath($input->getArgument('cnd-file'));

        if (!file_exists($cnd_file)) {
            throw new \InvalidArgumentException(
                sprintf("Node type definition file '<info>%s</info>' does not exist.", $cnd_file)
            );
        } elseif (!is_readable($cnd_file)) {
            throw new \InvalidArgumentException(
                sprintf("Node type definition file '<info>%s</info>' does not have read permissions.", $cnd_file)
            );
        }

        $cnd = file_get_contents($cnd_file);
        $allowUpdate = $input->getOption('allow-update');
        $session = $this->getHelper('phpcr')->getSession();

        $this->updateFromCnd($input, $output, $session, $cnd, $allowUpdate);

        $output->write(PHP_EOL.sprintf('Successfully registered node types from "<info>%s</info>"', $cnd_file) . PHP_EOL);
    }

    /**
     * Actually do the update.
     *
     * @param SessionInterface $session the phpcr session to talk to
     * @param string $cnd the compact namespace and node type definition in string form
     *
     * @throws \PHPCR\NodeType\NodeTypeExistsException if the node already exists and allowUpdate is false
     * @throws \PHPCR\RepositoryException on other errors
     */
    protected function updateFromCnd(InputInterface $input, OutputInterface $output, SessionInterface $session, $cnd, $allowUpdate)
    {
        if (! $session instanceof \Jackalope\Session) {
            throw new \Exception('PHPCR only provides an API to register node types. Your implementation is not Jackalope (which provides a method for .cnd). TODO: parse the file and do the necessary API calls');
        }
        $ntm = $session->getWorkspace()->getNodeTypeManager();

        try {
            $ntm->registerNodeTypesCnd($cnd, $allowUpdate);
        } catch (\PHPCR\NodeType\NodeTypeExistsException $e) {
            if (!$allowUpdate) {
                $output->write(PHP_EOL.'<error>The node type(s) you tried to register already exist.</error>'.PHP_EOL);
                $output->write(PHP_EOL.'If you want to override the existing definition call this command with the ');
                $output->write('<info>--allow-update</info> option.'.PHP_EOL);
            }
            throw $e;
        }
    }
}

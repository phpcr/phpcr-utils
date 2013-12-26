<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to list all node types
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodeTypeListCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:node-type:list')
            ->setDescription('List all available node types in the repository')
            ->setHelp(<<<EOT
This command lists all of the available node types and their subtypes
in the PHPCR repository.
EOT
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getPhpcrSession();
        $ntm = $session->getWorkspace()->getNodeTypeManager();

        $nodeTypes = $ntm->getAllNodeTypes();

        foreach ($nodeTypes as $name => $nodeType) {
            $output->writeln('<info>'.$name.'</info>');

            $superTypes = $nodeType->getSupertypeNames();
            if (count($superTypes)) {
                $output->writeln('  <comment>Supertypes:</comment>');
                foreach ($superTypes as $stName) {
                    $output->writeln('    <comment>></comment> '.$stName);
                }
            }
        }

        return 0;
    }
}

<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\PathNotFoundException;
use PHPCR\Util\PathHelper;

/**
 * Command to create a PHPCR node of a specified type from
 * the command line..
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodeTouchCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->configureNodeManipulationInput();

        $this->setName('phpcr:node:touch')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path at which to create the new node'
            )
            ->addOption(
                'type', 't',
                InputOption::VALUE_OPTIONAL,
                'Node type, default nt:unstructured',
                'nt:unstructured'
            )
            ->addOption('dump', 'd',
                InputOption::VALUE_NONE,
                'Dump a string reperesentation of the created / modified node.'
            )
            ->setDescription('Create or modify a node')
            ->setHelp(<<<HERE
This command allows you to create or modify a node at the specified path.

For example::

  $ ./bin/phpcr phpcr:touch /foobar --type=my:nodetype --set-prop=foo=bar

Will create the node "/foobar" and set (or create) the "foo" property
with a value of "bar".

You can execute the command again to further modify the node. Here we add
the property "bar" and remove the property "foo". We also add the dump option
to output a string reperesentation of the node.

  $ ./bin/phpcr phpcr:touch /foobar --type=my:nodetype --set-prop=bar=myvalue --remove-prop=foo --dump
HERE
);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getPhpcrCliHelper();
        $session = $this->getPhpcrSession();

        $path = $input->getArgument('path');
        $type = $input->getOption('type');
        $dump = $input->getOption('dump');

        $setProp = $input->getOption('set-prop');
        $removeProp = $input->getOption('remove-prop');
        $addMixins = $input->getOption('add-mixin');
        $removeMixins = $input->getOption('remove-mixin');

        try {
            $node = $session->getNode($path);
        } catch (PathNotFoundException $e) {
            $node = null;
        }

        if ($node) {
            $nodeType = $node->getPrimaryNodeType()->getName();
            $output->writeln(sprintf(
                '<info>Node at path </info>%s <info>already exists and has primary type</info> %s.',
                $path,
                $nodeType
            ));

            if ($nodeType != $type) {
                throw new \Exception(sprintf(
                    'You have specified node type "%s" but the existing node is of type "%s"',
                    $type, $nodeType
                ));
            }
        } else {

            $nodeName = PathHelper::getNodeName($path);
            $parentPath = PathHelper::getParentPath($path);

            try {
                $parentNode = $session->getNode($parentPath);
            } catch (PathNotFoundException $e) {
                $output->writeln(sprintf(
                    '<error>Parent path "%s" does not exist</error>',
                    $parentPath
                ));

                return;
            }

            $output->writeln(sprintf(
                '<info>Creating node: </info> %s [%s]', $path, $type
            ));

            $node = $parentNode->addNode($nodeName, $type);
        }

        $helper->processNode($output, $node, array(
            'setProp' => $setProp,
            'removeProp' => $removeProp,
            'addMixins' => $addMixins,
            'removeMixins' => $removeMixins,
            'dump' => $dump,
        ));

        $session->save();
    }
}

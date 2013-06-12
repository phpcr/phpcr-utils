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

use PHPCR\PropertyInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\PathNotFoundException;

/**
 * Command to create a PHPCR node of a specified type from
 * the command line..
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
            ->addOption('set-prop', 'p',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Set node property, use foo=bar'
            )
            ->addOption('remove-prop', 'r',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Remove node property'
            )
            ->addOption('dump', 'd',
                InputOption::VALUE_NONE,
                'Dump a string reperesentation of the created / modified node.'
            )
            ->addOption('add-mixin', null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Add a mixin to the node'
            )
            ->addOption('remove-mixin', null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Add a mixin to the node'
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
        $setProp = $input->getOption('set-prop');
        $removeProp = $input->getOption('remove-prop');
        $dump = $input->getOption('dump');
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

            $nodeName = basename($path);
            $parentPath = dirname($path);

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
            'setProps' => $setProp,
            'removeProps' => $removeProp,
            'addMixins' => $addMixins,
            'removeMixins' => $removeMixins,
            'dump' => $dump,
        ));

        $session->save();
    }
}

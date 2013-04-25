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
class TouchCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcr:touch')
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
            ->addOption('set', 's', 
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 
                'Set node property, use foo=bar'
            )
            ->addOption('unset', 'r', 
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 
                'Remove node property'
            )
            ->addOption('dump', 'd', 
                InputOption::VALUE_NONE, 
                'Dump a string reperesentation of the created / modified node.'
            )
            ->setDescription('Create or modify a node')
            ->setHelp(<<<HERE
This command allows you to create or modify a node at the specified path.

For example::

  $ ./bin/phpcr phpcr:touch /foobar --type=my:nodetype --set=foo=bar

Will create the node "/foobar" and set (or create) the "foo" property
with a value of "bar".

You can execute the command again to further modify the node. Here we add
the property "bar" and remove the property "foo". We also add the dump option
to output a string reperesentation of the node.

  $ ./bin/phpcr phpcr:touch /foobar --type=my:nodetype --set=bar=myvalue --unset=foo --dump
HERE
);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();
        $path = $input->getArgument('path');
        $type = $input->getOption('type');
        $sets = $input->getOption('set');
        $unsets = $input->getOption('unset');
        $dump = $input->getOption('dump');

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

        foreach ($sets as $set) {
            $parts = explode('=', $set);
            $output->writeln(sprintf(
                '<comment> > Setting property </comment>%s<comment> to </comment>%s',
                $parts[0], $parts[1]
            ));
            $node->setProperty($parts[0], $parts[1]);
        }

        foreach ($unsets as $unset) {
            $output->writeln(sprintf(
                '<comment> > Unsetting property </comment>%s',
                $unset
            ));
            $node->setProperty($unset, null);
        }

        if ($dump) {
            $output->writeln('<info>Node dump: </info>');
            foreach ($node->getProperties() as $property) {
                $value = $property->getValue();
                if (!is_string($value)) {
                    $value = print_r($value, true);
                }
                $output->writeln(sprintf('<comment> - %s = </comment>%s',
                    $property->getName(),
                    $value
                ));
            }
        }

        $session->save();
    }
}

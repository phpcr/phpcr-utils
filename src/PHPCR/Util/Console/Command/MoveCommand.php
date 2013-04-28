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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to move a node from one path to another
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class MoveCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:move')
            ->addArgument('source', InputArgument::REQUIRED, 'Path of node to move')
            ->addArgument('destination', InputArgument::REQUIRED, 'Destination for node')
            ->setDescription('Moves a node from one path to another')
            ->setHelp(<<<EOF
This command simply moves a node from one path (the source path) 
to another (the destination path), it can also be considered
as a rename command.

    $ php bin/phpcr phpcr:move /foobar /barfoo

Note that the parent node of the destination path must already exist.
EOF
            )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $sourcePath = $input->getArgument('source');
        $destPath = $input->getArgument('destination');

        $output->writeln(sprintf(
            '<info>Moving </info>%s<info> to </info>%s',
            $sourcePath, $destPath
        ));

        $session->move($sourcePath, $destPath);
        $session->save();
    }

}

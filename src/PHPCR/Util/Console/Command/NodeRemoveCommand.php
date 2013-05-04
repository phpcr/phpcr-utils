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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;

use PHPCR\Util\NodeHelper;

/**
 * Command to remove all nodes from a path in the workspace of the configured
 * session.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class NodeRemoveCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('phpcr:node:remove')
            ->setDescription('Remove content from the repository')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path of the node to purge', '/')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Use to bypass the confirmation dialog')
            ->addOption('only-children', null, InputOption::VALUE_NONE, 'Use to only purge children of specified path')
            ->setHelp(<<<EOF
The <info>phpcr:node:remove</info> command will remove the given node or the 
children of the given node according to the options given.

Remove specified node and its children:

    $ php bin/phpcr phpcr:node:remove /cms/content/blog

Remove only the children of the specified node

    $ php bin/phpcr phpcr:node:remove /cms/content/blog --only-children
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

        $path = $input->getArgument('path');
        $force = $input->getOption('force');
        $onlyChildren = $input->getOption('only-children');

        if (!$force) {
            $dialog = new DialogHelper();
            $workspaceName = $session->getWorkspace()->getName();

            if ($onlyChildren) {
                $question = 
                    'Are you sure you want to recursively delete the children of path "%s" '.
                    'from workspace "%s"';
            } else {
                $question = 
                    'Are you sure you want to recursively delete the path "%s" '.
                    'from workspace "%s"';
            }

            $force = $dialog->askConfirmation($output, sprintf(
                '<question>'.$question.' Y/N ?</question>', $path, $workspaceName, false
            ));
        }

        if ($force) {
            $message = '<comment>></comment> <info>Purging: </info>%s';

            if ($onlyChildren) {
                $baseNode = $session->getNode($path, 0);

                foreach ($baseNode->getNodes() as $childNode) {
                    $output->writeln(sprintf($message, $childNode->getPath()));
                    $childNode->remove();
                }
            } else {
                $output->writeln(sprintf($message, $path));

                if ('/' === $path) {
                    throw new \Exception(
                        'Will not purge path entire workspace ("/"), use the '.
                        'workspace:purge method instead.'
                    );
                } else {
                    $session->removeItem($path);
                }
            }

            $session->save();
        } else {
            $output->writeln('<error>Aborted</error>');
        }

        return 0;
    }
}

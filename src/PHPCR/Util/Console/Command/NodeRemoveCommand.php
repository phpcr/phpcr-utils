<?php

declare(strict_types=1);

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Exception\InvalidArgumentException as CliInvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to remove all nodes from a path in the workspace of the configured
 * session.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodeRemoveCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('phpcr:node:remove')
            ->setDescription('Remove content from the repository')
            ->addArgument('path', InputArgument::REQUIRED, 'Path of the node to purge')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Use to bypass the confirmation dialog')
            ->addOption('only-children', null, InputOption::VALUE_NONE, 'Use to only purge children of specified path')
            ->setHelp(
                <<<'EOF'
                    The <info>phpcr:node:remove</info> command will remove the given node or the
                    children of the given node according to the options given.

                    Remove specified node and its children:

                        $ php bin/phpcr phpcr:node:remove /cms/content/blog

                    Remove only the children of the specified node

                        $ php bin/phpcr phpcr:node:remove /cms/content/blog --only-children
                    EOF
            );
    }

    /**
     * @throws CliInvalidArgumentException
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $session = $this->getPhpcrSession();

        $path = $input->getArgument('path');
        $force = $input->getOption('force');
        $onlyChildren = $input->getOption('only-children');

        if ('/' === $path) {
            // even if we have only children, this will not work as we would
            // try to remove system nodes.
            throw new \InvalidArgumentException(
                'Can not delete root node (path "/"), please use the '.
                'workspace:purge command instead to purge the whole workspace.'
            );
        }

        if (!$force) {
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
            $confirmationQuestion = new ConfirmationQuestion(sprintf('<question>'.$question.' Y/N ?</question>', $path, $workspaceName), false);
            $force = $this->getQuestionHelper()->ask($input, $output, $confirmationQuestion);
        }

        if (!$force) {
            $output->writeln('<error>Aborted</error>');

            return 1;
        }

        $message = '<comment>></comment> <info>Removing: </info>%s';

        if ($onlyChildren) {
            $baseNode = $session->getNode($path, 0);

            foreach ($baseNode->getNodes() as $childNode) {
                $childNodePath = $childNode->getPath();
                $childNode->remove();
                $output->writeln(sprintf($message, $childNodePath));
            }
        } else {
            $session->removeItem($path);
            $output->writeln(sprintf($message, $path));
        }

        $session->save();

        return 0;
    }
}

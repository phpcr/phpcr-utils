<?php

declare(strict_types=1);

namespace PHPCR\Util\Console\Command;

use PHPCR\Util\NodeHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to remove all non-system nodes and properties in the workspace of
 * the configured session.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Leech <daniel@dantleech.com>
 */
class WorkspacePurgeCommand extends BaseCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('phpcr:workspace:purge')
            ->setDescription('Remove all nodes from a workspace')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Use to bypass the confirmation dialog')
            ->setHelp(
                <<<'EOF'
                    The <info>phpcr:workspace:purge</info> command removes all nodes except the
                    system nodes and all non-system properties of the root node from the workspace.
                    EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $session = $this->getPhpcrSession();
        $force = $input->getOption('force');

        $workspaceName = $session->getWorkspace()->getName();
        if (!$force) {
            $confirmationQuestion = new ConfirmationQuestion(sprintf(
                '<question>Are you sure you want to purge workspace "%s" Y/N ?</question>',
                $workspaceName
            ), false);
            $force = $this->getQuestionHelper()->ask($input, $output, $confirmationQuestion);
        }

        if (!$force) {
            $output->writeln('<error>Aborted</error>');

            return 1;
        }

        $output->writeln(sprintf('<info>Purging workspace:</info> %s', $workspaceName));

        // Using the static NodeHelper is bad for testing as we cannot mock it.
        NodeHelper::purgeWorkspace($session);
        $session->save();

        return 0;
    }
}

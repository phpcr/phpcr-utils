<?php

declare(strict_types=1);

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to move a node from one path to another.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodeMoveCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('phpcr:node:move')
            ->addArgument('source', InputArgument::REQUIRED, 'Path of node to move')
            ->addArgument('destination', InputArgument::REQUIRED, 'Destination for node')
            ->setDescription('Moves a node from one path to another')
            ->setHelp(
                <<<'EOF'
                    This command simply moves a node from one path (the source path)
                    to another (the destination path), it can also be considered
                    as a rename command.

                        $ php bin/phpcr phpcr:move /foobar /barfoo

                    Note that the parent node of the destination path must already exist.
                    EOF
            );
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $session = $this->getPhpcrSession();

        $sourcePath = $input->getArgument('source');
        $destPath = $input->getArgument('destination');

        $output->writeln(sprintf(
            '<info>Moving </info>%s<info> to </info>%s',
            $sourcePath,
            $destPath
        ));

        $session->move($sourcePath, $destPath);
        $session->save();

        return 0;
    }
}

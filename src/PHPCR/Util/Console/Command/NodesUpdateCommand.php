<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\Query\QueryResultInterface;
use PHPCR\Query\RowInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException as CliInvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command which can update the properties of nodes found
 * using the given JCR query.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Leech <daniel@dantleech.com>
 */
class NodesUpdateCommand extends BaseNodeManipulationCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->configureNodeManipulationInput();

        $this->setName('phpcr:nodes:update')
            ->addOption(
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Query used to select the nodes'
            )
            ->addOption(
                'query-language',
                'l',
                InputOption::VALUE_OPTIONAL,
                'The query language (e.g. sql, jcr_sql2)',
                'jcr-sql2'
            )
            ->addOption(
                'persist-counter',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Save the session every x requests',
                '100'
            )
            ->setDescription('Command to manipulate the nodes in the workspace.')
            ->setHelp(
                <<<HERE
The <info>phpcr:nodes:update</info> can manipulate the properties of nodes
found using the given query.

For example, to set the property <comment>foo</comment> to <comment>bar</comment> on all unstructured nodes:

    <info>php bin/phpcr phpcr:nodes:update --query="SELECT * FROM [nt:unstructured]" --set-prop=foo=bar</info>

Or to update only nodes matching a certain criteria:

    <info>php bin/phpcr phpcr:nodes:update \
        --query="SELECT * FROM [nt:unstructured] WHERE [phpcr:class]=\"Some\\Class\\Here\"" \
        --add-mixin=mix:mimetype</info>

The options for manipulating nodes are the same as with the
<info>node:touch</info> command and
can be repeated to update multiple properties.

If you have an advanced use case you can use the <comment>--apply-closure</comment> option:

    <info>php bin/phpcr phpcr:nodes:update \
        --query="SELECT * FROM [nt:unstructured] WHERE [phpcr:class]=\"Some\\Class\\Here\"" \
        --apply-closure="\\\$session->doSomething(); \\\$node->setProperty('foo', 'bar');"</info>

For each node in the result set, the closure will be passed the current
<comment>PHPCR\SessionInterface</comment> implementation and the node (<comment>PHPCR\NodeInterface</comment>) as <comment>\$session</comment> and <comment>\$node</comment>.
HERE
            );
    }

    /**
     * @throws CliInvalidArgumentException
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = $input->getOption('query');
        $queryLanguage = strtoupper($input->getOption('query-language'));
        $persistCounter = (int) $input->getOption('persist-counter');
        $setProp = $input->getOption('set-prop');
        $removeProp = $input->getOption('remove-prop');
        $addMixins = $input->getOption('add-mixin');
        $removeMixins = $input->getOption('remove-mixin');
        $applyClosures = $input->getOption('apply-closure');
        $noInteraction = $input->getOption('no-interaction');
        $helper = $this->getPhpcrHelper();
        $session = $this->getPhpcrSession();

        if (!$query) {
            throw new \InvalidArgumentException(
                'You must provide a SELECT query, e.g. --query="SELECT * FROM [nt:unstructured]"'
            );
        }

        if (0 !== stripos($query, 'SELECT')) {
            throw new \InvalidArgumentException("Query doesn't look like a SELECT query: '$query'");
        }

        $query = $helper->createQuery($queryLanguage, $query);
        $result = $query->execute();

        if (!$noInteraction) {
            if (false === $this->shouldExecute($input, $output, $result)) {
                return 0;
            }
        }

        $persistIn = $persistCounter;

        /** @var RowInterface $row */
        foreach ($result as $i => $row) {
            $output->writeln(sprintf(
                '<info>Updating node:</info> [%d] %s.',
                $i,
                $row->getPath()
            ));

            $node = $row->getNode();

            $helper->processNode($output, $node, [
                'setProp' => $setProp,
                'removeProp' => $removeProp,
                'addMixins' => $addMixins,
                'removeMixins' => $removeMixins,
                'applyClosures' => $applyClosures,
            ]);

            --$persistIn;
            if (0 === $persistIn) {
                $output->writeln('<info>Saving nodes processed so far...</info>');
                $session->save();
                $persistIn = $persistCounter;
            }
        }

        $output->writeln('<info>Saving session...</info>');
        $session->save();
        $output->writeln('<info>Done.</info>');

        return 0;
    }

    private function shouldExecute(InputInterface $input, OutputInterface $output, QueryResultInterface $result): bool
    {
        $question = new ConfirmationQuestion(sprintf(
            '<question>About to update %d nodes. Enter "Y" to continue, "N" to cancel or "L" to list.</question>',
            count($result->getRows())
        ));

        $response = $this->getQuestionHelper()->ask($input, $output, $question);
        if ('L' === $response) {
            /** @var RowInterface $row */
            foreach ($result as $i => $row) {
                $output->writeln(sprintf(' - [%d] %s', $i, $row->getPath()));
            }

            return $this->shouldExecute($input, $output, $result);
        }

        if ('N' === $response) {
            return false;
        }

        if ('Y' === $response) {
            return true;
        }

        return $this->shouldExecute($input, $output, $result);
    }
}

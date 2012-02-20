<?php

namespace PHPCR\Util\Console\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\Util\TreeWalker;
use PHPCR\Util\Console\Helper\ConsoleParametersParser;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperNodeVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\ConsoleDumperPropertyVisitor;
use PHPCR\Util\Console\Helper\TreeDumper\SystemNodeFilter;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class DumpCommand extends Command
{
    private $dump_max_line_length = 120;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('phpcr:dump')
            ->addOption('sys_nodes', null, InputOption::VALUE_OPTIONAL, 'Set to "yes" to dump the system nodes', "no")
            ->addOption('props', null, InputOption::VALUE_OPTIONAL, 'Set to "yes" to dump the node properties', "no")
            //TODO: implement ->addOption('recurse', null, InputOption::VALUE_OPTIONAL, 'Set to a number to limit how deep into the tree to recurse', "-1")
            ->addArgument('path', InputArgument::OPTIONAL, 'Path of the node to dump', '/')
            ->setDescription('Dump the content repository')
            ->setHelp(<<<EOF
The <info>dump</info> command recursively outputs the name of the node specified
by the <info>path</info> argument and its subnodes in a yaml-like style.

If the <info>props</info> option is set to yes the nodes properties are
displayed as yaml arrays.
By default the command filters out system nodes and properties (i.e. nodes and
properties with names starting with 'jcr:'), the <info>sys_nodes</info> option
allows to turn this filter off.
EOF
            )
        ;
    }

    /**
     * Change at which length lines in the dump get cut.
     */
    public function setDumpMaxLineLength($length)
    {
        $this->dump_max_line_length = $length;
    }

    /**
     * Executes the dump command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return integer 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $session = $this->getHelper('phpcr')->getSession();

        $path = $input->getArgument('path');

        $nodeVisitor = new ConsoleDumperNodeVisitor($output);

        $propVisitor = null;
        if (ConsoleParametersParser::isTrueString($input->getOption('props'))) {
            $propVisitor = new ConsoleDumperPropertyVisitor($output);
        }

        $walker = new TreeWalker($nodeVisitor, $propVisitor);

        if (! ConsoleParametersParser::isTrueString($input->getOption('sys_nodes'))) {
            $filter = new SystemNodeFilter();
            $walker->addNodeFilter($filter);
            $walker->addPropertyFilter($filter);
        }

        // do not catch error but let user see the node was not found
        $walker->traverse($session->getNode($path));

        return 0;
    }

}

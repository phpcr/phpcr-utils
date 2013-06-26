<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\Util\Console\Helper\PhpcrCliHelper;

abstract class BaseCommand extends Command
{
    /**
     * @return SessionInterface
     */
    protected function getPhpcrSession()
    {
        return $this->getHelper('phpcr')->getSession();
    }

    /**
     * @return PhpcrCliHelper
     */
    protected function getPhpcrCliHelper()
    {
        $phpcrCliHelper = new PhpcrCliHelper($this->getPhpcrSession());
        return $phpcrCliHelper;
    }

    public function configureNodeManipulationInput()
    {
        $this->addOption('set-prop', 'p',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Set node property on nodes use foo=bar'
        );
        $this->addOption('remove-prop', 'r',
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Remove property from nodes'
        );
        $this->addOption('add-mixin', null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Add a mixin to the nodes'
        );
        $this->addOption('remove-mixin', null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Remove mixin from the nodes'
        );
    }
}

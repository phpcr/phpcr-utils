<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\Util\Console\Helper\PhpcrCliHelper;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseCommand extends Command
{
    protected $phpcrCliHelper;

    /**
     * @return PHPCR\SessionInterface
     */
    protected function getPhpcrSession()
    {
        return $this->getHelper('phpcr')->getSession();
    }

    /**
     * @return PHPCR\Util\Console\Helper\PhpcrCliHelper
     */
    protected function getPhpcrCliHelper()
    {
        if (!$this->phpcrCliHelper) {
            $this->phpcrCliHelper = new PhpcrCliHelper($this->getPhpcrSession());
        }

        return $this->phpcrCliHelper;
    }

    /**
     * Hack to enable overriding for unit tests.
     */
    public function setPhpcrCliHelper(PhpcrCliHelper $helper)
    {
        $this->phpcrCliHelper = $helper;
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

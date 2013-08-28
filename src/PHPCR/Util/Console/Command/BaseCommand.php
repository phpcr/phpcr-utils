<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\SessionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use PHPCR\Util\Console\Helper\PhpcrCliHelper;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;

/**
 * Common base class to help with the helpers.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
abstract class BaseCommand extends Command
{
    protected $phpcrCliHelper;
    protected $phpcrConsoleDumperHelper;

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
        if (!$this->phpcrCliHelper) {
            $this->phpcrCliHelper = new PhpcrCliHelper($this->getPhpcrSession());
        }

        return $this->phpcrCliHelper;
    }

    /**
     * @return PhpcrConsoleDumperHelper
     */
    protected function getPhpcrConsoleDumperHelper()
    {
        if (!$this->phpcrConsoleDumperHelper) {
            $this->phpcrConsoleDumperHelper = new PhpcrConsoleDumperHelper();
        }

        return $this->phpcrConsoleDumperHelper;
    }

    public function setPhpcrConsoleDumperHelper($consoleDumperHelper)
    {
        $this->phpcrConsoleDumperHelper = $consoleDumperHelper;
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
        $this->addOption('apply-closure', null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Apply a closure to each node, closures are passed PHPCR\Session and PHPCR\NodeInterface'
        );
    }
}

<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Input\InputOption;

/**
 * Base command for node manipulations, providing common setup.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
abstract class BaseNodeManipulationCommand extends BaseCommand
{
    /**
     * Set up the options to manipulate nodes.
     */
    protected function configureNodeManipulationInput()
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

<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\SessionInterface;
use PHPCR\Util\Console\Helper\PhpcrHelper;
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
        return $this->getPhpcrHelper()->getSession();
    }

    /**
     * @return PhpcrHelper
     */
    protected function getPhpcrHelper()
    {
        return $this->getHelperSet()->get('phpcr');
    }

    /**
     * @return PhpcrConsoleDumperHelper
     */
    protected function getPhpcrConsoleDumperHelper()
    {
        return $this->getHelperSet()->get('phpcr_console_dumper');
    }
}

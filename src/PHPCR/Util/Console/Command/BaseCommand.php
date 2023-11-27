<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\SessionInterface;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;

/**
 * Common base class to help with the helpers.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
abstract class BaseCommand extends Command
{
    protected function getPhpcrSession(): SessionInterface
    {
        return $this->getPhpcrHelper()->getSession();
    }

    protected function getPhpcrHelper(): PhpcrHelper
    {
        return $this->getHelper('phpcr');
    }

    protected function getPhpcrConsoleDumperHelper(): PhpcrConsoleDumperHelper
    {
        return $this->getHelper('phpcr_console_dumper');
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}

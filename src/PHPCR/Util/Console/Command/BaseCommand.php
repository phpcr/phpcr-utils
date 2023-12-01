<?php

declare(strict_types=1);

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
        $helper = $this->getHelper('phpcr');
        if (!$helper instanceof PhpcrHelper) {
            throw new \RuntimeException('phpcr must be the PhpcrHelper');
        }

        return $helper;
    }

    protected function getPhpcrConsoleDumperHelper(): PhpcrConsoleDumperHelper
    {
        $helper = $this->getHelper('phpcr_console_dumper');
        if (!$helper instanceof PhpcrConsoleDumperHelper) {
            throw new \RuntimeException('phpcr_console_dumper must be the PhpcrConsoleDumperHelper');
        }

        return $helper;
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        $helper = $this->getHelper('question');
        if (!$helper instanceof QuestionHelper) {
            throw new \RuntimeException('question must be the QuestionHelper');
        }

        return $helper;
    }
}

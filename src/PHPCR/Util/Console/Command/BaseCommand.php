<?php

namespace PHPCR\Util\Console\Command;

use PHPCR\SessionInterface;
use PHPCR\Util\Console\Helper\PhpcrConsoleDumperHelper;
use PHPCR\Util\Console\Helper\PhpcrHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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

    /**
     * Ask a question with the question helper or the dialog helper for symfony < 2.5 compatibility.
     *
     * @param string $questionText
     * @param string $default
     *
     * @return string
     */
    protected function ask(InputInterface $input, OutputInterface $output, $questionText, $default = null)
    {
        if ($this->getHelperSet()->has('question')) {
            $question = new Question($questionText, $default);

            return $this->getQuestionHelper()->ask($input, $output, $question);
        }

        return $this->getDialogHelper()->ask($output, $questionText, $default);
    }

    /**
     * Ask for confirmation with the question helper or the dialog helper for symfony < 2.5 compatibility.
     *
     * @param string $questionText
     * @param bool   $default
     *
     * @return string
     */
    protected function askConfirmation(InputInterface $input, OutputInterface $output, $questionText, $default = true)
    {
        if ($this->getHelperSet()->has('question')) {
            $question = new ConfirmationQuestion($questionText, $default);

            return $this->getQuestionHelper()->ask($input, $output, $question);
        }

        return $this->getDialogHelper()->askConfirmation($output, $questionText, $default);
    }

    private function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }

    private function getDialogHelper(): DialogHelper
    {
        return $this->getHelper('dialog');
    }
}

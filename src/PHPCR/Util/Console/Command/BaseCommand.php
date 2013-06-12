<?php

namespace PHPCR\Util\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PHPCR\Util\Console\Helper\PhpcrCliHelper;

abstract class BaseCommand extends Command
{
    protected function getPhpcrSession()
    {
        return $this->getHelper('phpcr')->getSession();
    }

    protected function getPhpcrCliHelper()
    {
        $phpcrCliHelper = new PhpcrCliHelper($this->getPhpcrSession());
        return $phpcrCliHelper;
    }
}

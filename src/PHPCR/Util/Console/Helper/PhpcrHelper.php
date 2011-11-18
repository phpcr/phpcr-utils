<?php

namespace PHPCR\Util\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use PHPCR\SessionInterface;

/**
 * Helper class to make the session instance available to console command
 */
class PhpcrHelper extends Helper
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * Constructor
     *
     * @param SessionInterface $session the session to use in commands
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getName()
    {
        return 'phpcr';
    }
}


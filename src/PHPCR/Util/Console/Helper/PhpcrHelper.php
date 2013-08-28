<?php

namespace PHPCR\Util\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use PHPCR\SessionInterface;

/**
 * Helper class to make the session instance available to console commands.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 */
class PhpcrHelper extends Helper
{
    /**
     * The session bound to this helper
     *
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

    /**
     * Get the session
     *
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'phpcr';
    }
}

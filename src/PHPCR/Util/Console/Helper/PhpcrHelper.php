<?php

/**
 * This file is part of the PHPCR Utils
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License 2.0
 * @link http://phpcr.github.com/
 */

namespace PHPCR\Util\Console\Helper;

use Symfony\Component\Console\Helper\Helper;
use PHPCR\SessionInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class to make the session instance available to console commands
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

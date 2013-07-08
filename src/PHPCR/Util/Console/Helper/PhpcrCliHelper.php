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
class PhpcrCliHelper extends Helper
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
        return 'phpcr_cli';
    }

    /**
     * Process - or update - a given node.
     * Provides common processing for both touch
     * and update commands.
     */
    public function processNode(OutputInterface $output, $node, $options)
    {
        $options = array_merge(array(
            'setProp' => array(),
            'removeProp' => array(),
            'addMixins' => array(),
            'removeMixins' => array(),
            'applyClosures' => array(),
            'dump' => false,
        ), $options);

        foreach ($options['setProp'] as $set) {
            $parts = explode('=', $set);
            $output->writeln(sprintf(
                '<comment> > Setting property </comment>%s<comment> to </comment>%s',
                $parts[0], $parts[1]
            ));
            $node->setProperty($parts[0], $parts[1]);
        }

        foreach ($options['removeProp'] as $unset) {
            $output->writeln(sprintf(
                '<comment> > Unsetting property </comment>%s',
                $unset
            ));
            $node->setProperty($unset, null);
        }

        foreach ($options['addMixins'] as $addMixin) {
            $output->writeln(sprintf(
                '<comment> > Adding mixin </comment>%s',
                $addMixin
            ));

            $node->addMixin($addMixin);
        }

        foreach ($options['removeMixins'] as $removeMixin) {
            $output->writeln(sprintf(
                '<comment> > Removing mixin </comment>%s',
                $removeMixin
            ));

            $node->removeMixin($removeMixin);
        }

        foreach ($options['applyClosures'] as $closureString) {
            $closure = create_function('$session, $node', $closureString);
            $output->writeln(sprintf(
                '<comment> > Applying closure: %s</comment>',
                strlen($closureString) > 75 ? substr($closureString, 0, 72).'...' : $closureString
            ));
            $closure($this->session, $node);
        }

        if ($options['dump']) {
            $output->writeln('<info>Node dump: </info>');
            /** @var $property PropertyInterface */
            foreach ($node->getProperties() as $property) {
                $value = $property->getValue();
                if (!is_string($value)) {
                    $value = print_r($value, true);
                }
                $output->writeln(sprintf('<comment> - %s = </comment>%s',
                    $property->getName(),
                    $value
                ));
            }
        }
    }

    /**
     * Create a PHPCR query using the given language and
     * query string.
     *
     * @param string Language type - SQL, SQL2
     * @param string JCR Query
     *
     * @return PHPCR/QueryInterface
     */
    public function createQuery($language, $sql)
    {
        $this->validateQueryLanguage($language);

        $session = $this->getSession();
        $qm = $session->getWorkspace()->getQueryManager();
        $language = strtoupper($language);
        $query = $qm->createQuery($sql, $language);

        return $query;
    }

    /**
     * Validate the given query language.
     *
     * @param string Language type
     *
     * @return null
     */
    protected function validateQueryLanguage($language)
    {
        $qm = $this->getSession()->getWorkspace()->getQueryManager();
        $langs = $qm->getSupportedQueryLanguages();
        if (!in_array($language, $langs)) {
            throw new \Exception(sprintf(
                'Query language "%s" not supported, available query languages: %s',
                $language, implode(',', $langs)
            ));
        }
    }
}

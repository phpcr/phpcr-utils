<?php

namespace PHPCR\Util\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Helper;

use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;
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

    /**
     * Process - or update - a given node.
     *
     * Provides common processing for both touch and update commands.
     *
     * @param OutputInterface $output     used for status updates.
     * @param NodeInterface   $node       the node to manipulate.
     * @param array           $operations to execute on that node.
     */
    public function processNode(OutputInterface $output, NodeInterface $node, array $operations)
    {
        $operations = array_merge(array(
            'setProp' => array(),
            'removeProp' => array(),
            'addMixins' => array(),
            'removeMixins' => array(),
            'applyClosures' => array(),
            'dump' => false,
        ), $operations);

        foreach ($operations['setProp'] as $set) {
            $parts = explode('=', $set);
            $output->writeln(sprintf(
                '<comment> > Setting property </comment>%s<comment> to </comment>%s',
                $parts[0], $parts[1]
            ));
            $node->setProperty($parts[0], $parts[1]);
        }

        foreach ($operations['removeProp'] as $unset) {
            $output->writeln(sprintf(
                '<comment> > Unsetting property </comment>%s',
                $unset
            ));
            $node->setProperty($unset, null);
        }

        foreach ($operations['addMixins'] as $addMixin) {
            $output->writeln(sprintf(
                '<comment> > Adding mixin </comment>%s',
                $addMixin
            ));

            $node->addMixin($addMixin);
        }

        foreach ($operations['removeMixins'] as $removeMixin) {
            $output->writeln(sprintf(
                '<comment> > Removing mixin </comment>%s',
                $removeMixin
            ));

            $node->removeMixin($removeMixin);
        }

        foreach ($operations['applyClosures'] as $closure) {
            if ($closure instanceof \Closure) {
                $output->writeln(
                    '<comment> > Applying closure</comment>'
                );
            } else {
                $closureString = $closure;
                $closure = create_function('$session, $node', $closure);
                $output->writeln(sprintf(
                    '<comment> > Applying closure: %s</comment>',
                    strlen($closureString) > 75 ? substr($closureString, 0, 72).'...' : $closureString
                ));
            }

            $closure($this->session, $node);
        }

        if ($operations['dump']) {
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
     * @param string $language Language type - SQL, SQL2
     * @param string $sql      JCR Query string
     *
     * @return \PHPCR\Query\QueryInterface
     */
    public function createQuery($language, $sql)
    {
        $language = $this->validateQueryLanguage($language);

        $session = $this->getSession();
        $qm = $session->getWorkspace()->getQueryManager();
        $query = $qm->createQuery($sql, $language);

        return $query;
    }

    /**
     * Check if this is a supported query language.
     *
     * @param string $language Language name.
     *
     * @throws \Exception if the language is not supported.
     */
    protected function validateQueryLanguage($language)
    {
        $qm = $this->getSession()->getWorkspace()->getQueryManager();
        $langs = $qm->getSupportedQueryLanguages();
        foreach ($langs as $lang) {
            if (strtoupper($lang) === strtoupper($language)) {
                return $lang;
            }
        }

        throw new \Exception(sprintf(
            'Query language "%s" not supported, available query languages: %s',
            $language, implode(',', $langs)
        ));
    }
}

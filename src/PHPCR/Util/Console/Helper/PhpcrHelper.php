<?php

declare(strict_types=1);

namespace PHPCR\Util\Console\Helper;

use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;
use PHPCR\Query\QueryInterface;
use PHPCR\SessionInterface;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class to make the session instance available to console commands.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 */
class PhpcrHelper extends Helper
{
    /**
     * The session bound to this helper.
     */
    protected SessionInterface $session;

    /**
     * Constructor.
     *
     * @param SessionInterface $session the session to use in commands
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Get the session.
     */
    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getName(): string
    {
        return 'phpcr';
    }

    /**
     * Process - or update - a given node.
     *
     * Provides common processing for both touch and update commands.
     *
     * @param OutputInterface       $output     used for status updates
     * @param NodeInterface         $node       the node to manipulate
     * @param array<string, string> $operations to execute on that node
     */
    public function processNode(OutputInterface $output, NodeInterface $node, array $operations): void
    {
        $operations = array_merge([
            'setProp' => [],
            'removeProp' => [],
            'addMixins' => [],
            'removeMixins' => [],
            'applyClosures' => [],
            'dump' => false,
        ], $operations);

        foreach ($operations['setProp'] as $set) {
            $parts = explode('=', $set);
            $output->writeln(sprintf(
                '<comment> > Setting property </comment>%s<comment> to </comment>%s',
                $parts[0],
                $parts[1]
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
                $closure = function (SessionInterface $session, NodeInterface $node) use ($closureString): void {
                    eval($closureString);
                };
                $output->writeln(sprintf(
                    '<comment> > Applying closure: %s</comment>',
                    strlen($closureString) > 75 ? substr($closureString, 0, 72).'...' : $closureString
                ));
            }

            $closure($this->session, $node);
        }

        if ($operations['dump']) {
            $output->writeln('<info>Node dump: </info>');
            /** @var PropertyInterface $property */
            foreach ($node->getProperties() as $property) {
                $value = $property->getValue();
                if (!is_string($value)) {
                    $value = print_r($value, true);
                }
                $output->writeln(sprintf(
                    '<comment> - %s = </comment>%s',
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
     */
    public function createQuery(string $language, string $sql): QueryInterface
    {
        $language = $this->validateQueryLanguage($language);

        $session = $this->getSession();
        $qm = $session->getWorkspace()->getQueryManager();

        return $qm->createQuery($sql, $language);
    }

    /**
     * Check if this is a supported query language.
     *
     * @param string $language language name
     *
     * @throws \Exception if the language is not supported
     */
    protected function validateQueryLanguage(string $language)
    {
        $qm = $this->getSession()->getWorkspace()->getQueryManager();
        $langs = $qm->getSupportedQueryLanguages();
        foreach ($langs as $lang) {
            if (0 === strcasecmp($lang, $language)) {
                return $lang;
            }
        }

        throw new \Exception(sprintf(
            'Query language "%s" not supported, available query languages: %s',
            $language,
            implode(',', $langs)
        ));
    }
}

<?php

namespace PHPCR\Util\Console\Helper\TreeDumper;

use Symfony\Component\Console\Output\OutputInterface;

use PHPCR\ItemInterface;
use PHPCR\PropertyInterface;
use PHPCR\PropertyType;

/**
 * TODO: this should base on the TraversingItemVisitor
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ConsoleDumperPropertyVisitor extends ConsoleDumperItemVisitor
{
    /**
     * Limit to cap lines at to avoid garbled output on long property values
     *
     * @var int
     */
    protected $maxLineLength;

    /**
     * Show the full path for each reference
     */
    protected $expandReferences;

    /**
     * Instantiate property visitor
     *
     * @param OutputInterface $output
     * @param int             $maxLineLength
     */
    public function __construct(OutputInterface $output, $options = array())
    {
        $options = array_merge(array(
            'max_line_length' => 120,
            'ref_format' => 'uuid',
        ), $options);

        parent::__construct($output);

        $this->maxLineLength = $options['max_line_length'];
        $this->refFormat = $options['ref_format'];
    }

    /**
     * Print information about this property
     *
     * @param ItemInterface $item the property to visit
     */
    public function visit(ItemInterface $item)
    {
        if (! $item instanceof PropertyInterface) {
            throw new \Exception(sprintf('Internal error: did not expect to visit a non-property object: %s', is_object($item) ? get_class($item) : $item));
        }

        $value = $item->getString();

        if (! is_string($value)) {
            $value = print_r($value, true);
        }

        if (strlen($value) > $this->maxLineLength) {
            $value = substr($value, 0, $this->maxLineLength) . '...';
        }

        $referrers = array();

        if (in_array($item->getType(), array(
            PropertyType::WEAKREFERENCE,
            PropertyType::REFERENCE
        ))) {
            $referenceStrings = array();

            if ('path' == $this->refFormat) {
                $references = (array) $item->getValue();

                foreach ($references as $reference) {
                    $referenceStrings[] = $reference->getPath();
                }
            } else {
                $referenceStrings = (array) $item->getString();
            }

            $value = '';
        }

        $value = str_replace(array("\n", "\t"), '', $value);

        $this->output->writeln(str_repeat('  ', $this->level + 1) . '- <info>' . $item->getName() . '</info> = ' . $value);

        if (isset($referenceStrings)) {
            foreach ($referenceStrings as $referenceString) {
                $this->output->writeln(sprintf(
                    '%s - <info>%s</info>: %s',
                    str_repeat('  ', $this->level + 1),
                    $this->refFormat,
                    $referenceString
                ));
            }
        }
    }

}

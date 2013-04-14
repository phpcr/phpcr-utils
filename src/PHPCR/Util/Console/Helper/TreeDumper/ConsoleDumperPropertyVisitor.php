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
            'expand_references' => false,
        ), $options);

        parent::__construct($output);

        $this->maxLineLength = $options['max_line_length'];
        $this->expandReferences = $options['expand_references'];
    }

    /**
     * Print information about this property
     *
     * @param ItemInterface $item the property to visit
     */
    public function visit(ItemInterface $item)
    {
        if (! $item instanceof PropertyInterface) {
            throw new \Exception("Internal error: did not expect to visit a non-node object: $item");
        }

        $value = $item->getString();

        if (! is_string($value)) {
            $value = print_r($value, true);
        }

        if (strlen($value) > $this->maxLineLength) {
            $value = substr($value, 0, $this->maxLineLength) . '...';
        }

        $referrers = array();

        if (true === $this->expandReferences) {
            if (in_array($item->getType(), array(
                PropertyType::WEAKREFERENCE, 
                PropertyType::REFERENCE
            ))) {
                $referrers = $item->getValue();
                if (!is_array($referrers)) {
                    $referrers = array($referrers);
                }
                $value = '';
            }
        }


        $value = str_replace(array("\n", "\t"), '', $value);

        $this->output->writeln(str_repeat('  ', $this->level + 1) . '- <info>' . $item->getName() . '</info> = ' . $value);

        foreach ($referrers as $referrer) {
            $this->output->writeln(str_repeat('  ', $this->level + 1). '   - <info>ref</info>: '.$referrer->getPath().'');
        }
    }
}

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
use PHPCR\NodeInterface;

/**
 * TODO: this should base on the TraversingItemVisitor
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
class ConsoleDumperNodeVisitor extends ConsoleDumperItemVisitor
{
    /**
     * Whether to print the UUIDs or not
     *
     * @var bool
     */
    protected $identifiers;

    /**
     * Instantiate the console dumper visitor
     *
     * @param OutputInterface $output
     * @param bool            $identifiers whether to output the node UUID
     */
    public function __construct(OutputInterface $output, $identifiers = false)
    {
        parent::__construct($output);
        $this->identifiers = $identifiers;
    }

    /**
     * Print information about the visited node.
     *
     * @param ItemInterface $item the node to visit
     */
    public function visit(ItemInterface $item)
    {
        if (! $item instanceof NodeInterface) {
            throw new \Exception("Internal error: did not expect to visit a non-node object: $item");
        }

        $name = $item->getName();

        if ($item->getDepth() == 0) {
            $name = 'ROOT';
        }

        $out = str_repeat('  ', $this->level)
            . '<comment>' . $name . '</comment>';
        if ($this->identifiers) {
            $identifier = $item->getIdentifier();
            if ($identifier) {
                $out .= "($identifier)";
            }
        }
        $out .= ':';
        $this->output->writeln($out);
    }
}

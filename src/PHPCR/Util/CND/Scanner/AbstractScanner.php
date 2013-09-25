<?php

namespace PHPCR\Util\CND\Scanner;

use PHPCR\Util\CND\Reader\ReaderInterface;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
abstract class AbstractScanner
{
    /**
     * @var TokenQueue
     */
    private $queue;

    protected $context;

    public function __construct(Context\ScannerContext $context)
    {
        $this->resetQueue();
        $this->context = $context;
    }

    public function resetQueue()
    {
        $this->queue = new TokenQueue();
    }

    /**
     * @param Token $token
     *
     * @return Token|void
     */
    public function applyFilters(Token $token)
    {
        foreach ($this->context->getTokenFilters() as $filter) {

            $token = $filter->filter($token);

            if (null === $token) {
                break;
            }
        }

        return $token;
    }

    protected function getQueue()
    {
        return $this->queue;
    }

    protected function addToken(ReaderInterface $reader, Token $token)
    {
        $token->setLine($reader->getCurrentLine());
        $token->setRow($reader->getCurrentColumn());

        if ($token = $this->applyFilters($token)) {
            $this->queue->add($token);
        }
    }
}

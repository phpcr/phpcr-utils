<?php

declare(strict_types=1);

namespace PHPCR\Util\CND\Scanner;

use PHPCR\Util\CND\Reader\ReaderInterface;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
abstract class AbstractScanner
{
    private TokenQueue $queue;

    protected Context\ScannerContext $context;

    public function __construct(Context\ScannerContext $context)
    {
        $this->resetQueue();
        $this->context = $context;
    }

    public function resetQueue(): void
    {
        $this->queue = new TokenQueue();
    }

    public function applyFilters(Token $token): ?Token
    {
        foreach ($this->context->getTokenFilters() as $filter) {
            $token = $filter->filter($token);

            if (null === $token) {
                break;
            }
        }

        return $token;
    }

    protected function getQueue(): TokenQueue
    {
        return $this->queue;
    }

    protected function addToken(ReaderInterface $reader, Token $token): void
    {
        $token->setLine($reader->getCurrentLine());
        $token->setRow($reader->getCurrentColumn());

        if ($token = $this->applyFilters($token)) {
            $this->queue->add($token);
        }
    }
}

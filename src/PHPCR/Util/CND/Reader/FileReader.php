<?php

declare(strict_types=1);

namespace PHPCR\Util\CND\Reader;

/**
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
final class FileReader extends BufferReader
{
    private string $path;

    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(string $path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf("Invalid file '%s'", $path));
        }

        $this->path = $path;

        parent::__construct(file_get_contents($path));
    }

    public function getPath(): string
    {
        return $this->path;
    }
}

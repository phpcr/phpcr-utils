<?php

namespace PHPCR\Util\CND\Reader;

/**
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author Nikola Petkanski <nikola@petkanski.com>
 */
class FileReader extends BufferReader
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @param string $filePath
     * @throws \InvalidArgumentException
     */
    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf("Invalid file '%s'", $filePath));
        }

        $this->filePath = $filePath;

        parent::__construct(file_get_contents($filePath));
    }

    /**
     * @deprecated use getFilePath() instead
     * @return string
     */
    public function getFileName()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}

<?php

namespace PHPCR\Util\CND\Reader;

class FileReader extends BufferReader
{
    protected $fileName;

    public function __construct($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \InvalidArgumentException(sprintf("Invalid file '%s'", $fileName));
        }

        $this->fileName = $fileName;

        parent::__construct(file_get_contents($fileName));
    }

    public function getFileName()
    {
        return $this->fileName;
    }
}

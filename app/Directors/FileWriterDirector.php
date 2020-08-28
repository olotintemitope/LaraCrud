<?php

namespace Laztopaz\Directors;

use Laztopaz\Contracts\FileWriterAbstractFactory;
use Laztopaz\Contracts\FileWriterInterface;

class FileWriterDirector implements FileWriterInterface
{
    /**
     * @var FileWriterAbstractFactory
     */
    private FileWriterAbstractFactory $fileWriter;

    public function __construct(FileWriterAbstractFactory $fileWriterAbstractFactory)
    {
        $this->fileWriter = $fileWriterAbstractFactory;
    }

    /**
     * Get the FileWriter Instance
     *
     * @return FileWriterAbstractFactory
     */
    public function getWriter(): FileWriterAbstractFactory
    {
        return $this->fileWriter;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->fileWriter->getFilename();
    }

    /**
     * Set the filename
     *
     * @param string $name
     */
    public function setFileName(string $name): void
    {
        $this->fileWriter->setFileName($name);
    }
}

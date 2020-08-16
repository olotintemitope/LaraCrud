<?php

namespace Laztopaz\Laracrud\Directors;

use Laztopaz\Laracrud\Contracts\FileWriterAbstractFactory;
use Laztopaz\Laracrud\Contracts\FileWriterInterface;

class FileWriterDirector extends FileWriterAbstractFactory implements FileWriterInterface
{
    /**
     * @var FileWriterAbstractFactory
     */
    private $fileWriter;

    public function __construct(FileWriterAbstractFactory $fileWriterAbstractFactory)
    {
        $this->fileWriter = $fileWriterAbstractFactory;
    }

    /**
     * Get the FileWriter Instance
     *
     * @return FileWriterAbstractFactory
     */
    public function getFileWriter(): FileWriterAbstractFactory
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

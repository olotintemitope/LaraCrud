<?php


namespace App\Services;


use App\Contracts\FileWriterAbstractFactory;
use App\Contracts\FileWriterInterface;

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
     * @return FileWriterAbstractFactory
     */
    public function getFileWriter(): FileWriterAbstractFactory
    {
        return $this->fileWriter;
    }

    public function getFilename(): string
    {
        return $this->fileWriter->getFilename();
    }

    public function setFileName(string $name): void
    {
        $this->fileWriter->setFileName($name);
    }
}

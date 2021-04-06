<?php

namespace Laztopaz\Services;

use Laztopaz\Contracts\FileWriterAbstractFactory;
use Laztopaz\Directors\FileWriterDirector;

final class ModelFileWriterService extends FileWriterAbstractFactory
{
    /**
     * @override
     * Get the information about the model directory
     *
     * @param string $modelName
     * @param string|null $modelDirectory
     * @return array
     */
    public static function getDirectoryInfo(string $modelName, ?string $modelDirectory): array
    {
        $defaultModelDirectory = static::getDefaultDirectory($modelDirectory, static::DEFAULT_LARAVEL_NAMESPACE);
        $modelPath = static::getWorkingDirectory($defaultModelDirectory, $modelName);

        return [$defaultModelDirectory, $modelPath];
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->fileName;
    }

    /**
     * @override
     * set the model filename
     * @param string $name
     * @param string $schemaMode
     */
    public function setFileName(string $name, ?string $schemaMode): void
    {
        $this->fileName = ucwords($name);
    }

    public function getDirectory(FileWriterDirector $fileWriterDirector): array
    {
        return $fileWriterDirector->getWriter()->getDirectory($fileWriterDirector);
    }
}

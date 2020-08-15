<?php

namespace App\Services;

use App\Contracts\FileWriterAbstractFactory;

final class ModelFileWriterService extends FileWriterAbstractFactory
{
    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename(): string
    {
        return "";
    }

    /**
     * @override
     * set the filename you want the file name to be
     * different from the Model.php type
     * @param string $name
     */
    public function setFileName(string $name): void
    {
    }

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
        $applicationNamespace = ucwords(explode('\\', static::class)[0]);
        $defaultModelDirectory = static::getDefaultDirectory($modelDirectory, $applicationNamespace);
        $modelPath = static::getWorkingDirectory($defaultModelDirectory, $modelName);

        return [$defaultModelDirectory, $modelPath];
    }
}

<?php

namespace App\Services;

use App\Contracts\FileWriterAbstractFactory;

final class ModelFileWriterService extends FileWriterAbstractFactory
{
    public function getFilename(): string
    {
        return "";
    }

    public function setFileName(string $name): void
    {
    }

    /**
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

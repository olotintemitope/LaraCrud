<?php

namespace App\Services;

use App\Contracts\ConstantInterface;
use app\Contracts\FileWriterAbstractFactory;

final class ModelFileWriter extends FileWriterAbstractFactory implements ConstantInterface
{
    /**
     * @param string $defaultModelDirectory
     * @param string $modelName
     * @return string
     */
    public static function geWorkingDirectory(string $defaultModelDirectory, string $modelName): string
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultModelDirectory, $modelName, static::FILE_EXTENSION
        );
    }

    public static function getDefaultDirectory(string $directory, string $applicationNamespace): string
    {
        return empty($directory)
            ? $applicationNamespace . DIRECTORY_SEPARATOR . static::DEFAULT_MODEL_FOLDER
            : $applicationNamespace . DIRECTORY_SEPARATOR . $directory;
    }
}

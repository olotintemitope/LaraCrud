<?php

namespace App\Services;

use App\Contracts\ConstantInterface;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class FileWriter implements ConstantInterface
{
    /**
     * @param $modelDirectory
     * @param $modelPath
     * @param $content
     */
    public static function write($modelDirectory, $modelPath, $content): void
    {
        if (!File::exists($modelPath)) {
            File::ensureDirectoryExists($modelDirectory);
            File::put($modelPath, trim($content), false);
        } else {
            throw new RuntimeException("{$modelPath} already exists");
        }
    }

    /**
     * @param string $defaultModelDirectory
     * @param string $modelName
     * @return string
     */
    public static function getModelWorkingDirectory(string $defaultModelDirectory, string $modelName): string
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultModelDirectory, $modelName, static::FILE_EXTENSION
        );
    }

    /**
     * @param $modelDirectory
     * @param $applicationNamespace
     * @return string
     */
    public static function getDefaultModelDirectory($modelDirectory, $applicationNamespace): string
    {
        return empty($modelDirectory)
            ? $applicationNamespace . DIRECTORY_SEPARATOR . static::DEFAULT_MODEL_FOLDER
            : $applicationNamespace . DIRECTORY_SEPARATOR . $modelDirectory;
    }

    /**
     * @param $modelPath
     * @return bool
     */
    public static function modelExists($modelPath): bool
    {
        return File::exists($modelPath);
    }
}

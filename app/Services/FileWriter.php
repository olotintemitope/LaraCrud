<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class FileWriter
{
    /**
     * @param $modelDirectory
     * @param $modelPath
     * @param $content
     */
    public static function write($modelDirectory, $modelPath, $content): void
    {
        File::ensureDirectoryExists($modelDirectory);

        if (!File::exists($modelPath)) {
            File::put( $modelPath , trim($content), false);
        } else {
           throw new RuntimeException("{$modelPath} already exists");
        }
    }

    public static function getModelWorkingDirectory(string $defaultModelDirectory, string $modelName): string
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultModelDirectory, $modelName, self::FILE_EXTENSION
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
            ? $applicationNamespace .DIRECTORY_SEPARATOR.self::DEFAULT_MODEL_FOLDER
            : $applicationNamespace .DIRECTORY_SEPARATOR .$modelDirectory;
    }
}

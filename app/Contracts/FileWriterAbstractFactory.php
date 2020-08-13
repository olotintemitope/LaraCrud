<?php


namespace App\Contracts;


use Illuminate\Support\Facades\File;
use RuntimeException;

abstract class FileWriterAbstractFactory implements ConstantInterface
{
    /**
     * @param $directory
     * @param $filePath
     * @param $content
     */
    public static function write($directory, $filePath, $content): void
    {
        if (!File::exists($filePath)) {
            File::ensureDirectoryExists($directory);
            File::put($filePath, trim($content), false);
        } else {
            throw new RuntimeException("{$filePath} already exists");
        }
    }

    /**
     * @param $filePath
     * @return bool
     */
    public static function fileExists($filePath): bool
    {
        return File::exists($filePath);
    }


    /**
     * @param string $defaultModelDirectory
     * @param string $modelName
     * @return string
     */
    public static function getWorkingDirectory(string $defaultModelDirectory, string $modelName): string
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultModelDirectory, $modelName, static::FILE_EXTENSION
        );
    }

    public static function getDefaultDirectory($directory = "", string $applicationNamespace = ""): string
    {
        return empty($directory)
            ? $applicationNamespace . DIRECTORY_SEPARATOR . static::DEFAULT_MODEL_FOLDER
            : $applicationNamespace . DIRECTORY_SEPARATOR . $directory;
    }

    abstract public function setFileName(string $name): void;

    abstract public function getFileName(): string;
}

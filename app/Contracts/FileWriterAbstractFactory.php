<?php


namespace app\Contracts;


use Illuminate\Support\Facades\File;
use RuntimeException;

abstract class FileWriterAbstractFactory
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
     * @param string $defaultDirectory
     * @param string $fileName
     * @return mixed
     */
    abstract public static function getWorkingDirectory(string $defaultDirectory, string $fileName);

    /**
     * @param string $directory
     * @param string $applicationNamespace
     * @return mixed
     */
    abstract public static function getDefaultDirectory(string $directory, string $applicationNamespace);
}

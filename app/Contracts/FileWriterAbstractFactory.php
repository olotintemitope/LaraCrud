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
    public static function modelExists($filePath): bool
    {
        return File::exists($filePath);
    }

    abstract public static function geWorkingDirectory(string $defaultDirectory, string $fileName);
    abstract public static function getDefaultDirectory(string $directory, string $applicationNamespace);
}

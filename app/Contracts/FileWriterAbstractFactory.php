<?php

namespace Laztopaz\Contracts;


use Illuminate\Support\Facades\File;
use Laztopaz\Directors\FileWriterDirector;
use RuntimeException;

abstract class FileWriterAbstractFactory implements ConstantInterface
{
    /**
     * @var string
     */
    protected string $fileName = "";

    protected BuilderServiceInterface $builderService;
    /**
     * @param $directory
     * @param $filePath
     * @param $content
     */
    public static function write($directory, $filePath, $content): void
    {
        if (!File::exists($filePath)) {
            File::ensureDirectoryExists($directory);
            File::put($filePath, trim($content));
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
     * Get the full path of the model
     *
     * @override
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

    /**
     * Get the default directory of the model
     *
     * @override
     * @param string $directory
     * @param string $applicationNamespace
     * @return string
     */
    public static function getDefaultDirectory($directory = "", string $applicationNamespace = ""): string
    {
        return empty($directory)
            ? $applicationNamespace . DIRECTORY_SEPARATOR . static::DEFAULT_MODEL_FOLDER
            : $applicationNamespace . DIRECTORY_SEPARATOR . $directory;
    }

    /**
     * Should be implemented to set the filename
     *
     * @param string $name
     * @param string $schemaMode
     */
    abstract public function setFileName(string $name, ?string $schemaMode): void;

    /**
     * Should be implemented to get the filename
     *
     * @return string
     */
    abstract public function getFileName(): string;

    /**
     * @param FileWriterDirector $fileWriterDirector
     * @return array
     */
    abstract public function getDirectory(FileWriterDirector $fileWriterDirector): array;
}

<?php


namespace app\Services;


use App\Contracts\ConstantInterface;
use app\Contracts\FileWriterAbstractFactory;

final class MigrationFileWriter extends FileWriterAbstractFactory implements ConstantInterface
{

    public static function getWorkingDirectory(string $defaultDirectory, string $fileName)
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultDirectory, $fileName, static::DEFAULT_MIGRATION_FOLDER
        );
    }

    public static function getDefaultDirectory(string $directory, string $applicationNamespace)
    {
        return $applicationNamespace . DIRECTORY_SEPARATOR . static::DEFAULT_MIGRATION_FOLDER;
    }
}

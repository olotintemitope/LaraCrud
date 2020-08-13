<?php


namespace App\Services;


use App\Contracts\ConstantInterface;
use App\Contracts\FileWriterAbstractFactory;

final class MigrationFileWriter extends FileWriterAbstractFactory implements ConstantInterface
{
    public static function getWorkingDirectory(string $defaultDirectory, string $fileName)
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultDirectory, $fileName, static::DEFAULT_MIGRATION_FOLDER
        );
    }

    public static function getDefaultDirectory($directory = "", string $applicationNamespace = "")
    {
        return getcwd() . DIRECTORY_SEPARATOR .static::DEFAULT_MIGRATION_FOLDER;
    }

    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }


    public function getFilename(string $name): string
    {
        return $this->getDatePrefix().'_'.$name.static::FILE_EXTENSION;
    }
}

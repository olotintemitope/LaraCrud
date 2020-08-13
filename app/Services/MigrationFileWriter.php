<?php


namespace App\Services;

use App\Contracts\FileWriterAbstractFactory;

final class MigrationFileWriter extends FileWriterAbstractFactory
{
    /**
     * @var string
     */
    protected $fileName = "";
    /**
     * @param string $defaultDirectory
     * @param string $fileName
     * @return string
     */
    public static function getWorkingDirectory(string $defaultDirectory, string $fileName): string
    {
        return sprintf(
            "%s/%s/%s%s",
            getcwd(), $defaultDirectory, $fileName, static::DEFAULT_MIGRATION_FOLDER
        );
    }

    /**
     * @param string $directory
     * @param string $applicationNamespace
     * @return string
     */
    public static function getDefaultDirectory($directory = "", string $applicationNamespace = ""): string
    {
        return getcwd() . DIRECTORY_SEPARATOR .static::DEFAULT_MIGRATION_FOLDER;
    }

    /**
     * @return false|string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * @param string $name
     * @return void
     */
    public function setFileName(string $name): void
    {
        $this->fileName = $this->getDatePrefix().'_'.$name.static::FILE_EXTENSION;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param FileWriterDirector $fileWriterDirector
     * @return array
     */
    public function getBaseDirectory(FileWriterDirector $fileWriterDirector): array
    {
        $migrationFulPath = $fileWriterDirector->getFileWriter()::getDefaultDirectory();
        $filePath = $migrationFulPath . DIRECTORY_SEPARATOR . $fileWriterDirector->getFileName();
        return array($migrationFulPath, $filePath);
    }
}

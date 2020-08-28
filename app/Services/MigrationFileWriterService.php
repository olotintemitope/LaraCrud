<?php

namespace Laztopaz\Services;

use Laztopaz\Contracts\FileWriterAbstractFactory;
use Laztopaz\Directors\FileWriterDirector;

final class MigrationFileWriterService extends FileWriterAbstractFactory
{
    /**
     * Get migration folder full path
     *
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
     * Get migration folder absolute path
     * @param string $directory
     * @param string $applicationNamespace
     * @return string
     */
    public static function getDefaultDirectory($directory = "", string $applicationNamespace = ""): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . static::DEFAULT_MIGRATION_FOLDER;
    }

    /**
     * Get the filename
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Set the filename
     *
     * @param string $name
     * @param string $schemaMode
     * @return void
     */
    public function setFileName(string $name, $schemaMode = 'create'): void
    {
        $this->fileName = strtolower(
            $this->getDatePrefix() . '_' . $schemaMode . '_' . str_replace(' ', '_', $name) . '_table' . static::FILE_EXTENSION
        );
    }

    /**
     * Get date string
     *
     * @return false|string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the migration path and the complete migration file absolute path
     *
     * @param FileWriterDirector $fileWriterDirector
     * @return array
     */
    public function getDirectory(FileWriterDirector $fileWriterDirector): array
    {
        $migrationFulPath = $fileWriterDirector->getWriter()::getDefaultDirectory();
        $filePath = $migrationFulPath . DIRECTORY_SEPARATOR . $fileWriterDirector->getFileName();
        return array($migrationFulPath, $filePath);
    }
}

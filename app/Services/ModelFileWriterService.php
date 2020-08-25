<?php

namespace Laztopaz\Services;

use Laztopaz\Builders\ModelServiceBuilder;
use Laztopaz\Contracts\FileWriterAbstractFactory;

final class ModelFileWriterService extends FileWriterAbstractFactory
{
    public function __construct(ModelServiceBuilder $modelServiceBuilder)
    {
        $this->builderService = $modelServiceBuilder;
    }

    /**
     * @override
     * Get the information about the model directory
     *
     * @param string $modelName
     * @param string|null $modelDirectory
     * @return array
     */
    public static function getDirectoryInfo(string $modelName, ?string $modelDirectory): array
    {
        $defaultModelDirectory = static::getDefaultDirectory($modelDirectory, static::DEFAULT_LARAVEL_NAMESPACE);
        $modelPath = static::getWorkingDirectory($defaultModelDirectory, $modelName);

        return [$defaultModelDirectory, $modelPath];
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->fileName;
    }

    /**
     * @override
     * set the filename you want the file name to be
     * different from the Model.php type
     * @param string $name
     */
    public function setFileName(string $name): void
    {
        $this->fileName = $name;
    }

    public function getModel(): ModelServiceBuilder
    {
        return $this->builderService;
    }
}

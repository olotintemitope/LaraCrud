<?php


namespace App\Services;

use App\Contracts\ModelServiceInterface;

class OutPutWriterDirector
{
    /**
     * @var $modelService
     */
    private $modelServiceInterface;

    public function __construct(ModelServiceInterface $modelServiceInterface)
    {
        $this->modelServiceInterface = $modelServiceInterface;
    }

    /**
     * @return string
     */
    public function buildFileContent(): string
    {
        return $this->modelServiceInterface->build();
    }

    public function getModel(): ModelServiceInterface
    {
        return $this->modelServiceInterface;
    }
}

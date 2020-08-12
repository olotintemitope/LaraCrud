<?php


namespace App\Services;


use App\Contracts\ModelServiceInterface;

class ModelOutPutWriter
{
    /**
     * @var $modelService
     */
    private $modelService;

    public function __construct(ModelServiceInterface $modelServiceInterface)
    {
        $this->modelService = $modelServiceInterface;
    }

    /**
     * @return string
     */
    public function buildFileContent(): string
    {
        return $this->modelService->builder();
    }

    public function getModel(): ModelServiceInterface
    {
        return $this->modelService;
    }
}

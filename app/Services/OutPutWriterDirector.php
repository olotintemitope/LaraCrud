<?php


namespace App\Services;

use App\Contracts\BuilderServiceInterface;

class OutPutWriterDirector
{
    /**
     * @var BuilderServiceInterface
     */
    private $builderService;

    public function __construct(BuilderServiceInterface $builderService)
    {
        $this->builderService = $builderService;
    }

    /**
     * @return string
     */
    public function writeFileContent(): string
    {
        return $this->builderService->build();
    }
}

<?php


namespace App\Directors;

use App\Contracts\BuilderServiceInterface;

class OutPutDirector
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
     * Get builder content
     * @return string
     */
    public function getFileContent(): string
    {
        return $this->builderService->build();
    }
}

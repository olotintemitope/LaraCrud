<?php

namespace App\Services;

use App\Contracts\FileWriterAbstractFactory;

final class ModelFileWriter extends FileWriterAbstractFactory
{
    public function getFilename(): string
    {
        return "";
    }

    public function setFileName(string $name): void
    {
    }
}

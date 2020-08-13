<?php

namespace App\Services;

use App\Contracts\ConstantInterface;
use App\Contracts\FileWriterAbstractFactory;

final class ModelFileWriter extends FileWriterAbstractFactory implements ConstantInterface
{
    public function getFilename(string $name): string
    {
        // TODO: Implement getFilename() method.
    }
}

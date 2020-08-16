<?php

namespace Laztopaz\Contracts;


interface FileWriterInterface
{
    public function getFileWriter(): FileWriterAbstractFactory;
}

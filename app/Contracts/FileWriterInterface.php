<?php

namespace Laztopaz\Laracrud\Contracts;


interface FileWriterInterface
{
    public function getFileWriter(): FileWriterAbstractFactory;
}

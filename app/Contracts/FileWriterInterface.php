<?php


namespace App\Contracts;


interface FileWriterInterface
{
    public function getFileWriter(): FileWriterAbstractFactory;
}

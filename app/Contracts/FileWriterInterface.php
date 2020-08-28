<?php

namespace Laztopaz\Contracts;


interface FileWriterInterface
{
    public function getWriter(): FileWriterAbstractFactory;
}

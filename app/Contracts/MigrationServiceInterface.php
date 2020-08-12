<?php


namespace app\Contracts;


interface MigrationServiceInterface
{
    public function buildMigration() :string;
}

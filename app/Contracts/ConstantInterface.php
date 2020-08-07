<?php

namespace App\Contracts;

/**
 * Interface ConstantTrait
 */
interface ConstantInterface
{
    public const DEFAULT_MODEL_FOLDER = "Models";
    public const FILE_EXTENSION = ".php";

    public const AVAILABLE_COLUMN_TYPES = [
        'bigIncrements' => 'Auto-incrementing UNSIGNED BIGINT (primary key) equivalent column.',
        'bigInteger' => 'BIGINT equivalent column.',
        'binary' => 'BLOB equivalent column.',
        'boolean' => 'BOOLEAN equivalent column.',
        'char' => 'CHAR equivalent column with an optional length.',
        'date' => 'DATE equivalent column.',
        'dateTime' => 'DATETIME equivalent column.',
        'dateTimeTz' => 'DATETIME (with timezone) equivalent column.',
        'decimal' => 'DECIMAL equivalent column with a precision (total digits) and scale (decimal digits).',
        'double' => 'DOUBLE equivalent column with a precision (total digits) and scale (decimal digits).',
        'enum' => 'ENUM equivalent column.',
        'float' => 'LOAT equivalent column with a precision (total digits) and scale (decimal digits).',
        'geometry' => 'GEOMETRY equivalent column.',
        'geometryCollection' => 'GEOMETRYCOLLECTION equivalent column.',
        'increments' => 'Auto-incrementing UNSIGNED INTEGER (primary key) equivalent column.',
        'integer' => 'INTEGER equivalent column.',
        'ipAddress' => 'IP address equivalent column.',
        'json' => 'JSON equivalent column.',
        'jsonb' => 'JSONB equivalent column',
        'lineString' => 'LINESTRING equivalent column',
        'longText' => 'LONGTEXT equivalent column',
        'macAddress' => 'MAC address equivalent column',
        'mediumIncrements' => 'Auto-incrementing UNSIGNED MEDIUMINT (primary key) equivalent column',
        'mediumInteger' => 'MEDIUMINT equivalent column',
        'mediumText' => 'MEDIUMTEXT equivalent column',
        'morphs' => 'Adds taggable_id UNSIGNED INTEGER and taggable_type VARCHAR equivalent columns',
        'multiLineString' => 'MULTILINESTRING equivalent column',
        'multiPoint' => 'MULTIPOINT equivalent column',
        'multiPolygon' => 'MULTIPOLYGON equivalent column',
        'nullableMorphs' => 'Adds nullable versions of morphs() columns',
        'nullableTimestamps' => 'Alias of timestamps() method',
        'point' => 'POINT equivalent column',
        'polygon' => 'POLYGON equivalent column',
        'rememberToken' => 'Adds a nullable remember_token VARCHAR(100) equivalent column',
        'smallIncrements' => 'Auto-incrementing UNSIGNED SMALLINT (primary key) equivalent column',
        'smallInteger' => 'SMALLINT equivalent column',
        'softDeletes' => 'Adds a nullable deleted_at TIMESTAMP equivalent column for soft deletes',
        'softDeletesTz' => 'Adds a nullable deleted_at TIMESTAMP (with timezone) equivalent column for soft deletes',
        'string' => 'VARCHAR equivalent column with a optional length',
        'text' => 'TEXT equivalent column',
        'time' => 'TIME equivalent column',
        'timeTz' => 'TIME (with timezone) equivalent column',
        'timestamp' => 'TIMESTAMP equivalent column',
        'timestampTz' => 'TIMESTAMP (with timezone) equivalent column',
        'timestamps' => 'Adds nullable created_at and updated_at TIMESTAMP equivalent columns',
        'timestampsTz' => 'Adds nullable created_at and updated_at TIMESTAMP (with timezone) equivalent columns',
        'tinyIncrements' => 'Auto-incrementing UNSIGNED TINYINT (primary key) equivalent column',
        'tinyInteger' => 'TINYINT equivalent column',
        'unsignedBigInteger' => 'UNSIGNED BIGINT equivalent column',
        'unsignedDecimal' => 'UNSIGNED DECIMAL equivalent column with a precision (total digits) and scale (decimal digits)',
        'unsignedInteger' => 'UNSIGNED INTEGER equivalent column',
        'unsignedMediumInteger' => 'UNSIGNED MEDIUMINT equivalent column',
        'unsignedSmallInteger' => 'UNSIGNED SMALLINT equivalent column',
        'unsignedTinyInteger' => 'UNSIGNED TINYINT equivalent column',
        'uuid' => 'UUID equivalent column',
        'year' => 'YEAR equivalent column',
    ];
}

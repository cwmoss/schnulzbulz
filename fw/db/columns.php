<?php

namespace xorc\db;

enum columns: string {

    case INT = 'I';
    case DATE = 'D';
    case DATETIME = 'DT';
    case BLOB = 'B';
    case TEXT = 'C';
    case BOOL = 'BL';
    case TIME = 'T';
    case FLOAT = 'F';

    public static function from_db(string $db_type): static {
        $type = strtolower($db_type);
        return match ($type) {
            str_contains($type, 'int') ? $type : null => static::INT,
            'time' => static::TIME,
            'timestamp' => static::TIME,
            'date' => static::DATE,
            'datetime' => static::DATETIME,
            default => static::TEXT,
        };
    }
}

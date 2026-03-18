<?php

namespace App\SelectColumns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class DefaultApplySelectColumns
{
    protected static array $columns = ['*'];
    public static function apply(Builder|Relation $query): Builder|Relation
    {
        return $query->select(static::$columns);
    }
}

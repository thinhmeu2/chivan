<?php

namespace App\SelectColumns\Category;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectDetailCategory extends DefaultApplySelectColumns
{
    protected static array $columns = ['id', 'parent_id', 'name', 'draw_dow', 'type', 'code', 'content'];
}

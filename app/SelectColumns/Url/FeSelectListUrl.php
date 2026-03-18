<?php

namespace App\SelectColumns\Url;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectListUrl extends DefaultApplySelectColumns
{
    protected static array $columns = ['slug', 'is_index', 'model_id'];
}

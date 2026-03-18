<?php

namespace App\SelectColumns\Url;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectDetailUrl extends DefaultApplySelectColumns
{
    protected static array $columns = ['slug', 'is_index', 'model_id', 'model_type', 'meta_title', 'meta_description', 'canonical'];
}

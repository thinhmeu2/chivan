<?php

namespace App\SelectColumns\Post;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectListPost extends DefaultApplySelectColumns
{
    protected static array $columns = ['id', 'post_category_id', 'name', 'description', 'image'];
}

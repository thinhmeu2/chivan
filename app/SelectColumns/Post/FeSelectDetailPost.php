<?php

namespace App\SelectColumns\Post;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectDetailPost extends DefaultApplySelectColumns
{
    protected static array $columns = ['id', 'post_category_id', 'name', 'description', 'content', 'image'];
}

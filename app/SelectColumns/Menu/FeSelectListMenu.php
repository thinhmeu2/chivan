<?php

namespace App\SelectColumns\Menu;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectListMenu extends DefaultApplySelectColumns
{
    protected static array $columns = ['id', 'parent_id', 'name', 'type', 'url', 'url_id', 'image', 'is_follow'];
}

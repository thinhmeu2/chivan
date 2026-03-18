<?php

namespace App\SelectColumns\Page;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectDetailPage extends DefaultApplySelectColumns
{
    protected static array $columns = ['id', 'name', 'content', 'type'];
}

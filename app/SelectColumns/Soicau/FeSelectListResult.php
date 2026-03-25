<?php

namespace App\SelectColumns\Soicau;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectListResult extends DefaultApplySelectColumns
{
    protected static array $columns = ['number', 'start_date', 'range_day', 'number_win', 'win_day'];
}

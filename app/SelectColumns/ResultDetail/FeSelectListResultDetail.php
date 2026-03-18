<?php

namespace App\SelectColumns\ResultDetail;

use App\SelectColumns\DefaultApplySelectColumns;

class FeSelectListResultDetail extends DefaultApplySelectColumns
{
    protected static array $columns = ['result_id', 'prize_code', 'position', 'number'];
}

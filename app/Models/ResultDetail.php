<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultDetail extends BaseModel
{

    protected $table = 'result_details';
    public function result(): BelongsTo
    {
        return $this->belongsTo(Result::class);
    }
}

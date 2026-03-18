<?php

namespace App\Models;

use App\Enums\CategoryTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends BaseModel
{
    use RevisionTrait, LibraryTrait, HasSeoTrait;
    protected $casts = [
        'draw_dow' => 'array'
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public static function parentRelation(): callable
    {
        return fn (self $m) => $m->parent();
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function childrenDow(): HasMany
    {
        return $this->children()->where('type', CategoryTypeEnum::Dayofweek);
    }
    public function childrenLottery(): HasMany
    {
        return $this->children()->where('type', CategoryTypeEnum::Lottery);
    }
    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
    public function scopeWhereDow(Builder $q, int $dow): Builder
    {
        return $q->whereRaw('instr(draw_dow, ?)', $dow);
    }
}

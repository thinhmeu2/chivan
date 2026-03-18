<?php

namespace App\Models;

use App\Enums\ResultPrizeCodeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends BaseModel
{

    protected $table = 'results';
    protected $casts = [
        'draw_date' => 'datetime',
    ];
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function result_details(): HasMany
    {
        return $this->hasMany(ResultDetail::class);
    }
    /**
     * Lấy kết quả đã format theo từng loại xổ số
     */
    public function getResults(): array
    {
        if (! $this->exists) {
            return [];
        }
        $details = $this->result_details
            ->groupBy('prize_code')
            ->map(function ($items) {
                return $items
                    ->sortBy('position')
                    ->pluck('number')
                    ->values()
                    ->toArray();
            })
            ->toArray();

        // Miền Bắc
        if ($this->category->code === 'XSMB') {
            return $this->formatXsmb($details);
        }

        // Các tỉnh khác
        return $this->formatProvince($details);
    }
    private function formatXsmb(array $details): array
    {
        $result = [];

        if (isset($details['madb'])) {
            $result['madb'] = $details['madb'];
        }

        for ($i = 0; $i <= 7; $i++) {
            $key = 'g' . $i;
            $result[$key] = $details[$key] ?? [];
        }

        return $result;
    }
    private function formatProvince(array $details): array
    {
        $result = [];
        $prizeResult = array_merge([ResultPrizeCodeEnum::Madb->value],ResultPrizeCodeEnum::getPrizeLoto());

        $prizes = array_keys($details);

        usort($prizes, function ($a, $b) {
            return (int) substr($b, 1) <=> (int) substr($a, 1);
        });

        foreach ($prizes as $prize) {
            $result[$prize] = in_array($prize, $prizeResult) ? $details[$prize] : $details[$prize][0];
        }

        return $result;
    }

}

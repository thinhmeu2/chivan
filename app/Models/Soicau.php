<?php

namespace App\Models;

use App\Keys\Base;
use Illuminate\Support\Carbon;

class Soicau extends BaseModel
{

    protected $table = 'soicau';
    protected $casts = [
        'number' => 'array',
        'number_win' => 'array',
        'start_date' => 'datetime',
    ];

    public function getLatestSoiCau(string $key): ?SoiCau
    {
        return $this->newQuery()
            ->where('key', $key)
            ->orderByDesc('start_date')
            ->first();
    }
    public function insertSoiCau(Base $model): SoiCau
    {
        return static::create($model->toArray());
    }
    public function updateSoiCau(int $id, array $data): int
    {
        return $this->newQuery()
            ->where('id', $id)
            ->update($data);
    }
    public function updateSoiCauV2(int $id, Base $model): int
    {
        return $this->newQuery()
            ->where('id', $id)
            ->update($model->toArray());
    }
    public function getListSoiCau(string $key, int $limit = 10): array
    {
        return $this->newQuery()
            ->where('key', $key)
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    public function resetResult(string $key, string $fromDate, ?string $toDate = null): int
    {
        $query = $this->newQuery()
            ->where('key', $key)
            ->whereDate('start_date', '>=', $fromDate);

        if ($toDate !== null) {
            $query->whereDate('start_date', '<=', $toDate);
        }

        return $query->update([
            'result' => null,
        ]);
    }
    public function deleteResult(string $key, int $limit = 10): int
    {
        $ids = $this->newQuery()
            ->where('key', $key)
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('id');

        return SoiCau::query()
            ->whereIn('id', $ids)
            ->delete();
    }

    public function getEndDate(): Carbon
    {
        return $this->start_date
            ->copy()
            ->addDays($this->range_day - 1);
    }
}

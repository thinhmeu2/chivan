<?php

namespace App\Models;

use App\Keys\Base;

class Soicau extends BaseModel
{

    protected $table = 'soicau';
    protected $casts = [
        'number' => 'array',
        'result' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function getLatestSoiCau(string $type): ?SoiCau
    {
        return SoiCau::query()
            ->where('type', $type)
            ->orderByDesc('start_date')
            ->first();
    }
    public function insertSoiCau(Base $model): SoiCau
    {
        return static::create($model->toArray());
    }
    public function updateSoiCau(int $id, array $data): int
    {
        return SoiCau::query()
            ->where('id', $id)
            ->update($data);
    }
    public function updateSoiCauV2(int $id, Base $model): int
    {
        return SoiCau::query()
            ->where('id', $id)
            ->update($model->toArray());
    }
    public function getListSoiCau(string $type, int $limit = 10): array
    {
        return SoiCau::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->limit($limit)
            ->get()
            ->toArray();
    }
    public function resetResult(string $key, string $fromDate, ?string $toDate = null): int
    {
        $query = SoiCau::query()
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
        $ids = SoiCau::query()
            ->where('type', $key)
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('id');

        return SoiCau::query()
            ->whereIn('id', $ids)
            ->delete();
    }
}

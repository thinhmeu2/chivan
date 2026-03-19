<?php

namespace App\Services\FeServices;

use App\Models\Key;
use App\SelectColumns\Key\FeSelectListKey;
use Carbon\Carbon;

class KeyService extends BaseService
{
    protected string $applierList   = FeSelectListKey::class;
    protected string $applierDetail = FeSelectListKey::class;

    public function __construct()
    {
        parent::__construct(resolve(Key::class));
    }
    public function getHtmlFromDb(string $key, Carbon $date): ?string
    {
        return $this->applierList::apply($this->newQuery())
            ->where('name', $key)
            ->where('date', $date->format('Y-m-d'))
            ->value('html');
    }
    public function saveHtml(string $key, Carbon $date, string $html): true
    {
        $this->model::query()->updateOrCreate(
            [
                'name' => $key,
                'date' => $date->format('Y-m-d'), // tránh lệch datetime
            ],
            [
                'html' => $html,
            ]
        );

        return true;
    }
}

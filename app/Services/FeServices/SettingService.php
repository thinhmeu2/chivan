<?php

namespace App\Services\FeServices;

use App\Enums\SettingGroupEnum;
use App\Models\Setting;
use App\SelectColumns\Setting\FeSelectListSetting;
use Illuminate\Support\Facades\Cache;

class SettingService extends BaseService
{
    protected string $applierList = FeSelectListSetting::class;
    protected string $applierDetail = FeSelectListSetting::class;

    private const CACHE_TTL = 86400;

    public function __construct()
    {
        parent::__construct(resolve(Setting::class));
    }

    public function getByGroup(SettingGroupEnum $group): array
    {
        $keyCache = "setting_group_" . $group->value;

        return Cache::remember($keyCache, self::CACHE_TTL, function () use ($group) {
            return $this->applierList::apply($this->newQuery())
                ->where('group', $group)
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}

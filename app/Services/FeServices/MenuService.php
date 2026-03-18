<?php
namespace App\Services\FeServices;

use App\DTO\MenuDto;
use App\Models\Menu;
use App\SelectColumns\Menu\FeSelectListMenu;
use Illuminate\Support\Facades\Cache;

class MenuService extends BaseService
{
    protected string $applierList = FeSelectListMenu::class;
    protected string $applierDetail = FeSelectListMenu::class;
    public function __construct()
    {
        parent::__construct(resolve(Menu::class));
    }

    public function dtoMenu(): array
    {
        return Cache::remember('menus', 86400, function () {
            // 1️⃣ Lấy toàn bộ menu 1 lần
            $menus = $this->applierList::apply($this->newQuery())->with('url')
                ->orderByDesc('order')
                ->get();
            /** group theo parent_id */
            $grouped = $menus->groupBy('parent_id');

            /** gắn children cho từng node */
            $menus->each(function ($menu) use ($grouped) {
                $menu->setRelation(
                    'children',
                    $grouped->get($menu->id, collect())
                );
            });

            /** lấy root (parent_id = null hoặc 0 tùy DB) */
            $tree = $grouped->get(null, collect());

            return MenuDto::from($tree);
        });
    }
}

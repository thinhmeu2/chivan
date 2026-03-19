<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;

class CollectionHelper
{
    public static function buildTree(Collection $collection): Collection
    {
        // index theo id cho tra nhanh, đỡ vòng lặp ngu học
        $itemsById = $collection->keyBy('id');

        // gắn children rỗng trước cho khỏi undefined
        foreach ($itemsById as $item) {
            $item->setRelation('children', new Collection());;
        }

        // tree gốc
        $tree = new Collection();

        foreach ($itemsById as $item) {
            if ($item->parent_id && $itemsById->has($item->parent_id)) {
                // có cha thì nhét vào children của cha
                $itemsById[$item->parent_id]->children->push($item);
            } else {
                // không có cha thì là root
                $tree->push($item);
            }
        }

        return $tree;
    }
}

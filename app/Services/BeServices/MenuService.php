<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Models\BaseModel;
use App\Models\Menu;

class MenuService extends BaseService
{
    public function __construct()
    {
        parent::__construct(resolve(Menu::class));
    }
    public function customNameAttribute(Menu $item): string
    {
        $name = '';
        if ($item->icon){
            $name .= "<img src=$item->image width=20 height=24>";
        }
        $name .= "$item->name ➜ $item->url";

        return $name;
    }
    public function saveByTreeNested(array $data): void
    {
        $menus = $this->getList()->get();
        $this->saveTrees($data, $menus);
    }

    public function trees(): \Illuminate\Support\Collection
    {
        return $this->buildTreeFromCollection($this->builder()->reorderDesc('order')->get());
    }


    protected function handleAfterCreate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleAfterCreate() method.
    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleBeforeUpdate() method.
    }
    protected function handleBeforeDelete(BaseModel|Menu $model)
    {
        $model->children()->update(['parent_id' => 0]);
    }

}

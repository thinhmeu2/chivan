<?php

namespace App\Services\BeServices;

use App\Enums\{ModelEnum, RevisionActionEnum};
use App\Http\Requests\Be\BaseBeRequest;
use App\Models\{Admin, BaseModel, Category, Page, Post, PostCategory, Redirect, Revision, RevisionDetail, Setting, Url};
use App\Services\{AuthService, LogService};

class RevisionService extends BaseService
{
    protected AuthService $authService;
    public array $save = [
        Admin::class => ['is_admin', 'is_active', 'role_id'],
        Category::class => ['parent_id', 'name', 'draw_dow', 'content', 'type', 'code'],
//        Menu::class => ['parent_id', 'name', 'type', 'url', 'url_id', 'is_follow'],
        Page::class => ['name', 'content', 'is_active'],
        Post::class => ['post_category_id', 'name', 'content', 'image', 'is_active'],
        PostCategory::class => ['name'],
        Redirect::class => ['from', 'to'],
        Setting::class => ['name', 'value'],
        Url::class => ['slug', 'is_index', 'meta_title', 'meta_description', 'canonical'],
    ];
    public function __construct()
    {
        parent::__construct(new Revision);
        $this->authService = resolve(AuthService::class);
    }

    public function saveRevision(BaseModel $model, RevisionActionEnum $action): void
    {
        $fields = $this->save[$model::class] ?? null;
        if (empty($fields)) {
            return;
        }
        if ($action === RevisionActionEnum::Update){
            if (empty(array_intersect(array_keys($model->getDirty()), $fields)))
                return;
        }

        // Insert bản ghi chính
        if ($action == RevisionActionEnum::Delete){
            $fieldName = ModelEnum::fromModel($model)->getFieldShowName();
            $nameDeleted = $model->{$fieldName};
            $this->deleteRevisionBeforeDeleteModel($model);
        }

        $admin_id = $this->authService->id();
        $revision = new Revision([
            'action'     => $action->value,
            'admin_id'   => $admin_id,
            'name_deleted' => $nameDeleted ?? null
        ]);
        if (!$admin_id){
            LogService::revision($revision->id. ' ' . url()->current());
        }

        $revision->model()->associate($model);
        $revision->save();

        if ($action === RevisionActionEnum::Update) {
            $details = [];

            foreach ($fields as $field) {
                if (! $model->isDirty($field)) {
                    continue;
                }

                $details[] = [
                    'revision_id' => $revision->id,
                    'field'      => $field,
                    'old_value'  => $model->getRawOriginal($field),
                ];
            }
            RevisionDetail::query()->insert($details);
        }
    }
    public function revert(Revision $revision, string $field): void
    {
        $detail = $revision->details()->where('field', $field)->first();
        if (! $detail)
            abort(404, "Không thấy giá trị cũ");
        $model = $revision->model->setAttribute($field, $detail->old_value);
        $model->save();
    }
    protected function deleteRevisionBeforeDeleteModel(BaseModel $model): void
    {
        $model->revisions()->where('action', '!=', RevisionActionEnum::Delete)->delete();
    }

    protected function handleBeforeDelete(BaseModel|Revision $model): void
    {
        /*revision_details đã set foreign key on_delete -> cascade*/
    }

    protected function handleAfterCreate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleAfterCreate() method.
    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleBeforeUpdate() method.
    }
}

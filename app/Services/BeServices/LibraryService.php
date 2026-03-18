<?php

namespace App\Services\BeServices;

use App\Http\Requests\Be\BaseBeRequest;
use App\Models\{BaseModel, Category, Library, Menu, Page, Post, Setting};
use App\Services\UploadService;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class LibraryService extends BaseService
{
    public $modelColumns = [
        Category::class => ['image', 'icon', 'banner', 'content'],
        Menu::class => ['icon'],
        Page::class => ['content'],
        Post::class => ['content', 'image'],
        Setting::class => ['value'],
    ];
    public function __construct()
    {
        parent::__construct(resolve(Library::class));
    }

    protected function handleBeforeDelete(BaseModel $model)
    {
        // TODO: Implement handleBeforeDelete() method.
    }

    protected function handleAfterCreate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleAfterCreate() method.
    }

    protected function handleBeforeUpdate(BaseModel $model, BaseBeRequest $data)
    {
        // TODO: Implement handleBeforeUpdate() method.
    }

    public function saveUploadFile(string $hashFileUpload, SymfonyFile $webpFile): Library
    {
        // absolute:  /var/www/storage/app/public/2025/11/24/abc.webp
        $absolutePath = $webpFile->getRealPath();

        // chuyển thành "2025/11/24/abc.webp"
        $relative = str_replace(UploadService::getPublicPath(), '', $absolutePath);

        // tách Y m d name
        [$year, $month, $day, $name] = explode(DIRECTORY_SEPARATOR, $relative);

        $library = $this->model->create([
            'year'      => (int) $year,
            'month'     => (int) $month,
            'day'       => (int) $day,
            'name'      => $name,
            'file_hash' => $hashFileUpload,
        ]);

        return $library;
    }
}

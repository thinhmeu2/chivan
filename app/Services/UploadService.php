<?php

namespace App\Services;

use App\Models\Library;
use App\Services\BeServices\LibraryService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class UploadService
{
    public static int $maxSizeImage = 2097152; // 2MB
    public static array $optionPost = ["16/9", 1200];
    public static array $optionIcon = ["1/1", 30];
    public static int $maxWidth = 1200;
    private static array $imageMimes = ['webp', 'jpg', 'png'];

    public static function getStorage(): Filesystem
    {
        return Storage::disk('public');
    }
    public static function getPublicPath(): string
    {
        return static::getStorage()->path('');
    }
    private static function validateFileUploaded(UploadedFile $file): void
    {
        $fileName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, static::$imageMimes, true)) {
            throw new \InvalidArgumentException(
                "File '{$fileName}' không hỗ trợ định dạng '{$ext}'."
            );
        }

        if ($file->getSize() > static::$maxSizeImage) {
            throw new \InvalidArgumentException(
                "File '{$fileName}' vượt quá dung lượng cho phép."
            );
        }
    }

    private static function convert2Webp(SymfonyFile|UploadedFile $file): InterventionImage
    {
        $path = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension() ?? $file->getExtension());

        $img = Image::make($path);
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $img->filename = static::getValidNameFile($originalName);

//        $img->filename = $file->getClientOriginalName();

        // Bước 1: Convert sang WebP nếu chưa phải
        if ($extension !== 'webp') {
            $img->encode('webp', 90);
        }

        // Bước 2: Resize nếu width > maxWidth
        if ($img->width() > static::$maxWidth) {
            $img->resize(static::$maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }

        return $img;
    }

    public static function getValidNameFile(string $nameFile): string
    {
        $baseName = Str::slug($nameFile);
        $baseName = preg_replace('#^\d{8}[_-]#', '', $baseName);
        return preg_replace('#^\d{4}-\d{4}-#', '', $baseName);
    }

    private static function getFileHash(SymfonyFile|UploadedFile $file): string
    {
        // Tính hash từ nội dung byte của tệp tạm thời
        return hash_file('md5', $file->getRealPath());
    }
    private static function getLibraryFromFileUpload(UploadedFile $file): ?Library
    {
        return Library::query()->where('file_hash', static::getFileHash($file))->first();
    }
    private static function saveImage(InterventionImage $image): SymfonyFile
    {
        $baseDir = storage_path('app/public'); // đổi tùy dự án
        $folder = date('Y/m/d');
        $fullDir = $baseDir . '/' . $folder;

        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0775, true);
        }

        // tên file cơ bản
        $name = pathinfo($image->filename ?? 'image', PATHINFO_FILENAME);
        if (!$name) {
            $name = 'image';
        }

        $filename = $name . '.webp';
        $path = $fullDir . '/' . $filename;

        // nếu trùng thì thêm -1, -2...
        $i = 1;
        while (file_exists($path)) {
            $filename = $name . '-' . $i . '.webp';
            $path = $fullDir . '/' . $filename;
            $i++;
        }

        // lưu ảnh
        $image->save($path, 90, 'webp');

        return new SymfonyFile($path);
    }
    public static function saveUploadFile(UploadedFile $file): Library
    {
        static::validateFileUploaded($file);
        $library = self::getLibraryFromFileUpload($file);
        if (! $library){
            $image = static::convert2Webp($file);
            $newImage = static::saveImage($image);
            $library = resolve(LibraryService::class)->saveUploadFile(static::getFileHash($file), $newImage);
        }
        return $library;
    }
}

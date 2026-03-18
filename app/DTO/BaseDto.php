<?php

namespace App\DTO;

use App\Helpers\KeyHelper;
use App\Models\BaseModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseDto
{
    public static array $requestCache = [];

    public static function from(
        Collection|LengthAwarePaginator|BaseModel|null $model
    ): array {
        if (! $model) {
            return [];
        }

        // Single model
        if ($model instanceof BaseModel) {
            $key = static::cacheKey($model);

            return static::$requestCache[$key]
                ??= new static($model)->toArray();
        }

        // Paginator → unwrap collection
        if ($model instanceof LengthAwarePaginator) {
            $model = $model->getCollection();
        }

        // Eloquent collection
        return $model->map(function (BaseModel $item) {
            $key = static::cacheKey($item);

            return static::$requestCache[$key]
                ??= new static($item)->toArray();
        })->all();
    }
    public static function fromReplace(BaseModel $model, array $dtoCategory): array
    {
        $model = static::from($model);
        $replaces = [
            '[ma-tinh]'  => strtoupper($dtoCategory['code']),
            '[ten-tinh]' => $dtoCategory['name'],
        ];
        KeyHelper::replace($model, $replaces);

        $code = strtolower($dtoCategory['code']);
        if ($code != 'xsmb')
            $model['url'] .= "-{$code}";

        return $model;
    }

    private function toArray(): array
    {
        return get_object_vars($this);
    }

    protected function formatContentBeforeShow(?string $content): string
    {
        if (! $content) return '';
        return str_replace(['<table', '</table>'], ['<div class="overflow-auto"><table', '</table></div>'], $content);
    }
    protected function addToc(?string &$content): array
    {
        if (!$content) return [];

        $toc = [];

        $content = preg_replace_callback('#<h([234])[^>]*>(.*?)</h\1>#', function ($m) use (&$toc) {
            $level = (int) $m[1];
            $title = trim(strip_tags($m[2]));
            $id = 'toc-' . Str::slug($title);

            if ($level === 2) {
                // Cấp 1
                $toc[] = [
                    'id' => $id,
                    'title' => $title,
                    'children' => []
                ];
            } elseif ($level === 3) {
                // Cấp 2
                if (!empty($toc)) {
                    $tocIndex = count($toc) - 1;
                    $toc[$tocIndex]['children'][] = [
                        'id' => $id,
                        'title' => $title,
                        'children' => []
                    ];
                }
            } else {
                // Cấp 3 (h4)
                if (!empty($toc)) {
                    $tocIndex = count($toc) - 1;
                    if (!empty($toc[$tocIndex]['children'])) {
                        $subIndex = count($toc[$tocIndex]['children']) - 1;
                        $toc[$tocIndex]['children'][$subIndex]['children'][] = [
                            'id' => $id,
                            'title' => $title
                        ];
                    }
                }
            }

            // Thêm id vào heading
            return preg_replace(
                '#<h([234])([^>]*)>#',
                "<h$1 id=\"$id\"$2>",
                $m[0],
                1
            );
        }, $content);

        return $toc;
    }
    private static function cacheKey(BaseModel $model): string
    {
        return static::class . ':' . $model->getKey();
    }
}

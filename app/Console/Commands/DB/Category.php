<?php

namespace App\Console\Commands\DB;

use App\Console\Commands\BaseCommand;
use App\Enums\CategoryTypeEnum;
use App\Services\BeServices\CategoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Category extends BaseCommand
{
    protected $signature = 'db:categories';
    private array $categories = [
        [
            'name' => 'Miền Bắc',
            'draw_dow' => '[1, 2, 3, 4, 5, 6, 7]',
            'type' => CategoryTypeEnum::RegionVsLottery->value,
            'code' => 'XSMB',
            'children' => [
                [
                    'name'      => 'Miền Bắc thứ 2',
                    'draw_dow'  => '[1]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
                [
                    'name'      => 'Miền Bắc thứ 3',
                    'draw_dow'  => '[2]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
                [
                    'name'      => 'Miền Bắc thứ 4',
                    'draw_dow'  => '[3]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
                [
                    'name'      => 'Miền Bắc thứ 5',
                    'draw_dow'  => '[4]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
                [
                    'name'      => 'Miền Bắc thứ 6',
                    'draw_dow'  => '[5]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
                [
                    'name'      => 'Miền Bắc thứ 7',
                    'draw_dow'  => '[6]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
                [
                    'name'      => 'Miền Bắc chủ nhật',
                    'draw_dow'  => '[7]',
                    'type'      => CategoryTypeEnum::Dayofweek->value,
                    'code'      => 'XSMB',
                ],
            ]
        ],
        [
            'name' => 'Miền Trung',
            'draw_dow' => '[1, 2, 3, 4, 5, 6, 7]',
            'type' => CategoryTypeEnum::Region->value,
            'code' => 'XSMT',
            'children' => [
                // day of week
                [
                    'name' => 'Miền Trung thứ 2',
                    'draw_dow' => '[1]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],
                [
                    'name' => 'Miền Trung thứ 3',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],
                [
                    'name' => 'Miền Trung thứ 4',
                    'draw_dow' => '[3]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],
                [
                    'name' => 'Miền Trung thứ 5',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],
                [
                    'name' => 'Miền Trung thứ 6',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],
                [
                    'name' => 'Miền Trung thứ 7',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],
                [
                    'name' => 'Miền Trung chủ nhật',
                    'draw_dow' => '[7]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMT',
                ],

                // lottery
                [
                    'name' => 'Xổ số Đắk Lắk',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSDLK',
                ],
                [
                    'name' => 'Xổ số Đà Nẵng',
                    'draw_dow' => '[3, 6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSDNA',
                ],
                [
                    'name' => 'Xổ số Đắk Nông',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSDNO',
                ],
                [
                    'name' => 'Xổ số Gia Lai',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSGL',
                ],
                [
                    'name' => 'Xổ số Khánh Hòa',
                    'draw_dow' => '[3, 7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSKH',
                ],
                [
                    'name' => 'Xổ số Kon Tum',
                    'draw_dow' => '[7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSKT',
                ],
                [
                    'name' => 'Xổ số Ninh Thuận',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSNT',
                ],
                [
                    'name' => 'Xổ số Phú Yên',
                    'draw_dow' => '[1]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSPY',
                ],
                [
                    'name' => 'Xổ số Quảng Bình',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSQB',
                ],
                [
                    'name' => 'Xổ số Quảng Ngãi',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSQNG',
                ],
                [
                    'name' => 'Xổ số Quảng Trị',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSQT',
                ],
                [
                    'name' => 'Xổ số Thừa Thiên Huế',
                    'draw_dow' => '[1, 7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSTTH',
                ],
                [
                    'name' => 'Xổ số Bình Định',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSBDI',
                ],
                [
                    'name' => 'Xổ số Quảng Nam',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSQNA',
                ],
            ],
        ],
        [
            'name' => 'Miền Nam',
            'draw_dow' => '[1, 2, 3, 4, 5, 6, 7]',
            'type' => CategoryTypeEnum::Region->value,
            'code' => 'XSMN',
            'children' => [
                // day of week
                [
                    'name' => 'Miền Nam thứ 2',
                    'draw_dow' => '[1]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],
                [
                    'name' => 'Miền Nam thứ 3',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],
                [
                    'name' => 'Miền Nam thứ 4',
                    'draw_dow' => '[3]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],
                [
                    'name' => 'Miền Nam thứ 5',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],
                [
                    'name' => 'Miền Nam thứ 6',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],
                [
                    'name' => 'Miền Nam thứ 7',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],
                [
                    'name' => 'Miền Nam chủ nhật',
                    'draw_dow' => '[7]',
                    'type' => CategoryTypeEnum::Dayofweek->value,
                    'code' => 'XSMN',
                ],

                // lottery
                [
                    'name' => 'Xổ số An Giang',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSAG',
                ],
                [
                    'name' => 'Xổ số Bình Dương',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSBD',
                ],
                [
                    'name' => 'Xổ số Bạc Liêu',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSBL',
                ],
                [
                    'name' => 'Xổ số Bình Phước',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSBP',
                ],
                [
                    'name' => 'Xổ số Bình Thuận',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSBTH',
                ],
                [
                    'name' => 'Xổ số Bến Tre',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSBTR',
                ],
                [
                    'name' => 'Xổ số Cà Mau',
                    'draw_dow' => '[1]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSCM',
                ],
                [
                    'name' => 'Xổ số Cần Thơ',
                    'draw_dow' => '[3]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSCT',
                ],
                [
                    'name' => 'Xổ số Đà Lạt',
                    'draw_dow' => '[7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSDL',
                ],
                [
                    'name' => 'Xổ số Đồng Nai',
                    'draw_dow' => '[3]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSDN',
                ],
                [
                    'name' => 'Xổ số Đồng Tháp',
                    'draw_dow' => '[1]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSDT',
                ],
                [
                    'name' => 'Xổ số Hồ Chí Minh',
                    'draw_dow' => '[1, 6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSHCM',
                ],
                [
                    'name' => 'Xổ số Hậu Giang',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSHG',
                ],
                [
                    'name' => 'Xổ số Kiên Giang',
                    'draw_dow' => '[7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSKG',
                ],
                [
                    'name' => 'Xổ số Long An',
                    'draw_dow' => '[6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSLA',
                ],
                [
                    'name' => 'Xổ số Sóc Trăng',
                    'draw_dow' => '[3]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSST',
                ],
                [
                    'name' => 'Xổ số Tiền Giang',
                    'draw_dow' => '[7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSTG',
                ],
                [
                    'name' => 'Xổ số Tây Ninh',
                    'draw_dow' => '[4]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSTN',
                ],
                [
                    'name' => 'Xổ số Trà Vinh',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSTV',
                ],
                [
                    'name' => 'Xổ số Vĩnh Long',
                    'draw_dow' => '[5]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSVL',
                ],
                [
                    'name' => 'Xổ số Vũng Tàu',
                    'draw_dow' => '[2]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'XSVT',
                ],
            ],
        ],
        [
            'name' => 'Vietlott',
            'draw_dow' => '[1, 2, 3, 4, 5, 6, 7]',
            'type' => CategoryTypeEnum::Region->value,
            'code' => 'VIETLOTT',
            'children' => [
                [
                    'name' => 'Xổ số Mega 6/45',
                    'draw_dow' => '[3, 5, 7]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'MEGA',
                    'children' => [
                        [
                            'name' => 'Xổ số Mega 6/45 thứ 4',
                            'draw_dow' => '[3]',
                            'type' => CategoryTypeEnum::Dayofweek->value,
                            'code' => 'MEGA',
                        ],
                        [
                            'name' => 'Xổ số Mega 6/45 thứ 6',
                            'draw_dow' => '[5]',
                            'type' => CategoryTypeEnum::Dayofweek->value,
                            'code' => 'MEGA',
                        ],
                        [
                            'name' => 'Xổ số Mega 6/45 chủ nhật',
                            'draw_dow' => '[7]',
                            'type' => CategoryTypeEnum::Dayofweek->value,
                            'code' => 'MEGA',
                        ],
                    ]
                ],
                [
                    'name' => 'Xổ số Power 6/55',
                    'draw_dow' => '[2, 4, 6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'POWER',
                    'children' => [
                        [
                            'name'     => 'Xổ số Power 6/55 thứ 3',
                            'draw_dow'=> '[2]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'POWER',
                        ],
                        [
                            'name'     => 'Xổ số Power 6/55 thứ 5',
                            'draw_dow'=> '[4]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'POWER',
                        ],
                        [
                            'name'     => 'Xổ số Power 6/55 thứ 7',
                            'draw_dow'=> '[6]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'POWER',
                        ],
                    ]
                ],
                [
                    'name' => 'Xổ số Max 3D Plus',
                    'draw_dow' => '[1, 3, 5]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'MAX3D',
                    'children' => [
                        [
                            'name'     => 'Xổ số Max 3D Plus thứ 2',
                            'draw_dow'=> '[1]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'MAX3D',
                        ],
                        [
                            'name'     => 'Xổ số Max 3D Plus thứ 4',
                            'draw_dow'=> '[3]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'MAX3D',
                        ],
                        [
                            'name'     => 'Xổ số Max 3D Plus thứ 6',
                            'draw_dow'=> '[5]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'MAX3D',
                        ],
                    ]
                ],
                [
                    'name' => 'Xổ số Max 3D Pro',
                    'draw_dow' => '[2, 4, 6]',
                    'type' => CategoryTypeEnum::Lottery->value,
                    'code' => 'MAX3DPRO',
                    'children' => [
                        [
                            'name'     => 'Xổ số Max 3D Pro thứ 3',
                            'draw_dow'=> '[2]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'MAX3DPRO',
                        ],
                        [
                            'name'     => 'Xổ số Max 3D Pro thứ 5',
                            'draw_dow'=> '[4]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'MAX3DPRO',
                        ],
                        [
                            'name'     => 'Xổ số Max 3D Pro thứ 7',
                            'draw_dow'=> '[6]',
                            'type'     => CategoryTypeEnum::Dayofweek->value,
                            'code'     => 'MAX3DPRO',
                        ],
                    ]
                ],
            ],
        ]
    ];

    protected function executeCommand(): void
    {
        $this->insertCategories($this->categories);

        DB::statement("update categories set `name` = replace(`name`, 'Xổ số ', '')");
    }
    private function insertCategories(array $categories, ?int $parentId = null): void
    {
        $cateService = resolve(CategoryService::class);
        foreach ($categories as $item) {

            $children = $item['children'] ?? [];
            unset($item['children']);

            $item['parent_id'] = $parentId;
            // FIX draw_dow
            if (isset($item['draw_dow']) && is_string($item['draw_dow'])) {
                $item['draw_dow'] = json_decode($item['draw_dow'], true);
            }

            /** @var \App\Models\Category $category */
            $category = $cateService->create($item);

            // tạo url kèm theo
            $category->url()->create([
                'slug'             => Str::slug($item['name']),
                'meta_title'       => $item['name'],
                'meta_description' => $item['name'],
                'is_index' => true,
            ]);

            // đệ quy nếu có con
            if (!empty($children)) {
                $this->insertCategories($children, $category->id);
            }
        }
    }
}

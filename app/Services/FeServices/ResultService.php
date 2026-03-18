<?php

namespace App\Services\FeServices;

use App\DTO\Category\CategorySelectOptionDto;
use App\DTO\Result\ResultStatisticSpecialDto;
use App\Enums\CategoryTypeEnum;
use App\Enums\ResultPrizeCodeEnum;
use App\Models\Category;
use App\Models\Result;
use App\SelectColumns\Category\FeSelectListCategory;
use App\SelectColumns\Result\FeSelectListResult;
use App\SelectColumns\ResultDetail\FeSelectListResultDetail;
use App\SelectColumns\Url\FeSelectListUrl;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ResultService extends BaseService
{
    protected string $applierList   = FeSelectListResult::class;
    protected string $applierDetail = FeSelectListResult::class;

    public function __construct()
    {
        parent::__construct(resolve(Result::class));
    }

    public function getByCategory(Category $category, int $limit = 1): ?Collection
    {
        return $this->handleQuery($category, null, $limit);
    }

    public function getDetail(Category $category, Carbon $date): ?Collection
    {
        $results = $this->handleQuery($category, $date, 1);

        if (! $results || $results->isEmpty()) {
            throw new \LogicException('Không tìm thấy kết quả theo ngày');
        }

        return $results;
    }

    /**
    |------------------------------------------------------------
    | Core handler dùng chung cho list & detail
    |------------------------------------------------------------
     */
    private function handleQuery(
        Category $category,
        ?Carbon $date = null,
        int $limit = 1
    ): ?Collection {
        /*
         |------------------------------------------------------------
         | STEP 1: normalize Dayofweek
         |------------------------------------------------------------
         */
        $dayOfWeek   = null;
        $categoryDow = null;

        if ($category->type === CategoryTypeEnum::Dayofweek->value) {

            $dayOfWeek   = $category->draw_dow[0] ?? null;
            $categoryDow = $category;

            $this->load($category, 'parent', true);

            if (
                ! $category->parent ||
                ! in_array(
                    $category->parent->getOriginal('type'),
                    array_merge(
                        CategoryTypeEnum::getTypeLottery(),
                        [CategoryTypeEnum::Region->value]
                    )
                )
            ) {
                return null;
            }

            $category = $category->parent;
        }

        /*
         |------------------------------------------------------------
         | STEP 2: build base query
         |------------------------------------------------------------
         */
        $categoryType = $category->getOriginal('type');

        $q = match (true) {

            in_array($categoryType, CategoryTypeEnum::getTypeLottery()) =>
            $this->buildLotteryQuery($category),

            $categoryType === CategoryTypeEnum::Region->value =>
            $this->buildRegionQuery($category, $limit, $date),

            default =>
            throw new \LogicException('Chưa hỗ trợ category type: ' . $categoryType),
        };

        if (! $q) {
            return null;
        }

        /*
         |------------------------------------------------------------
         | STEP 3: apply filters
         |------------------------------------------------------------
         */
        if ($dayOfWeek !== null) {
            $q->whereRaw('WEEKDAY(draw_date) + 1 = ?', [$dayOfWeek]);
        }

        if ($date) {
            $q->whereDate('draw_date', $date->toDateString());
        } else {
            $q->orderByDesc('draw_date');
        }

        if (! $date && $categoryType !== CategoryTypeEnum::Region->value) {
            $q->limit($limit);
        }

        $results = $q->with([
            'result_details' => fn ($q) => FeSelectListResultDetail::apply($q),
            'category' => fn ($q) => FeSelectListCategory::apply($q)->with([
                'url' => fn ($q) => FeSelectListUrl::apply($q),
            ]),
        ])->get();

        /*
         |------------------------------------------------------------
         | STEP 5: fix relations
         |------------------------------------------------------------
         */
        if (in_array($categoryType, CategoryTypeEnum::getTypeLottery())) {
            $results->each(
                fn ($result) => $result->setRelation('category', $category)
            );
        }

        if ($categoryDow) {
            $results->each(
                fn ($result) => $result->setRelation('categoryDow', $categoryDow)
            );
        }

        /*
         |------------------------------------------------------------
         | STEP 6: breadcrumb + group
         |------------------------------------------------------------
         */
        $this->appendDataForBreadcrumb($results);

        return $this->resultAfterGroup($category, $results);
    }

    /*
     |------------------------------------------------------------
     | Build Lottery Query
     |------------------------------------------------------------
     */
    private function buildLotteryQuery(Category $category)
    {
        return FeSelectListResult::apply(
            $category->results()
        );
    }

    /*
     |------------------------------------------------------------
     | Build Region Query
     |------------------------------------------------------------
     */
    private function buildRegionQuery(
        Category $category,
        int $limit,
        ?Carbon $date = null
    ) {
        $childCategoryIds = $category->children()->pluck('id');

        if ($childCategoryIds->isEmpty()) {
            return null;
        }

        $drawDatesQuery = $this->newQuery()
            ->whereIn('category_id', $childCategoryIds);

        if ($date) {
            $drawDatesQuery->whereDate('draw_date', $date->toDateString());
        }

        $drawDates = $drawDatesQuery
            ->select('draw_date')
            ->distinct()
            ->orderByDesc('draw_date')
            ->limit($limit)
            ->pluck('draw_date');

        if ($drawDates->isEmpty()) {
            return null;
        }

        return FeSelectListResult::apply(
            $this->newQuery()
                ->whereIn('category_id', $childCategoryIds)
                ->whereIn('draw_date', $drawDates)
        );
    }

    private function resultAfterGroup(Category $category, Collection $results): Collection
    {
        if (in_array($category->getOriginal('code'), ['XSMT', 'XSMN'])) {
            return $results->groupBy(fn ($item) => $item->getRawOriginal('draw_date'));
        }

        return $results;
    }

    private function appendDataForBreadcrumb(Collection|Result $result): void
    {
        if ($result instanceof Collection) {
            $result->each(fn ($i) => $this->appendDataForBreadcrumb($i));
            return;
        }

        if ($result->category->parent_id && !in_array($result->category->code, config('app.vietlott_codes'))) {
            $categoryRegion = $result->category->parent;

            $this->load($result->category, 'parent.url');
            $this->load($result->category->parent, 'childrenDow.url');

            $dowCategories = $result->category->parent->childrenDow;
        } else {
            $categoryRegion = $result->category;

            $this->load($result->category, 'childrenDow.url');

            $dowCategories = $result->category->childrenDow;
        }

        if (! $result->relationLoaded('categoryDow')) {
            $result->setRelation(
                'categoryDow',
                $dowCategories->first(
                    fn ($i) => in_array(
                        $result->draw_date->format('N'),
                        $i->getOriginal('draw_dow')
                    )
                )
            );
        }

        $result->setRelation('categoryRegion', $categoryRegion);
    }

    public function getLogan(Category $category, int $month = 1): array
    {
        $prizeCodes = [
            ResultPrizeCodeEnum::G0,
            ResultPrizeCodeEnum::G1,
            ResultPrizeCodeEnum::G2,
            ResultPrizeCodeEnum::G3,
            ResultPrizeCodeEnum::G4,
            ResultPrizeCodeEnum::G5,
            ResultPrizeCodeEnum::G6,
            ResultPrizeCodeEnum::G7,
            ResultPrizeCodeEnum::G8,
        ];

// chuyển enum -> value nếu cần
        $prizeCodeValues = array_map(
            static fn ($e) => $e->value,
            $prizeCodes
        );
        $prizeCodeValues = "'".implode("','", $prizeCodeValues)."'";

        $sql = <<<SQL
WITH
ordered_results AS (
    SELECT
        r.id,
        r.draw_date,
        ROW_NUMBER() OVER (ORDER BY r.draw_date) AS draw_idx
    FROM results r
    WHERE r.category_id = :category_id
      AND r.draw_date >= DATE_SUB(CURDATE(), INTERVAL :month MONTH)
),
lo_hits AS (
    SELECT
        RIGHT(rd.number, 2) AS lo,
        o.draw_idx,
        o.draw_date
    FROM ordered_results o
    JOIN result_details rd ON rd.result_id = o.id
    WHERE rd.prize_code IN ($prizeCodeValues)
),
middle_gaps AS (
    SELECT
        lo,
        CAST(draw_idx AS SIGNED)
        - CAST(LAG(draw_idx) OVER (PARTITION BY lo ORDER BY draw_idx) AS SIGNED)
        - 1 AS gap
    FROM lo_hits
),
start_gaps AS (
    SELECT
        lo,
        MIN(draw_idx) - 1 AS gap
    FROM lo_hits
    GROUP BY lo
),
current_gan AS (
    SELECT
        lo,
        MAX(draw_date) AS last_draw_date,
        (SELECT MAX(draw_idx) FROM ordered_results)
        - MAX(draw_idx) AS current_gan
    FROM lo_hits
    GROUP BY lo
),
past_max_gan AS (
    SELECT
        lo,
        MAX(gap) AS past_max_gan
    FROM (
        SELECT lo, gap FROM middle_gaps WHERE gap IS NOT NULL
        UNION ALL
        SELECT lo, gap FROM start_gaps
    ) t
    GROUP BY lo
)
SELECT
    c.lo,
    c.last_draw_date,
    c.current_gan,
    GREATEST(
        COALESCE(p.past_max_gan, 0),
        c.current_gan
    ) AS max_gan
FROM current_gan c
LEFT JOIN past_max_gan p ON p.lo = c.lo
ORDER BY c.current_gan DESC
LIMIT 10;
SQL;

        $rows = DB::select($sql, [
            'category_id' => $category->id,
            'month'       => $month,
        ]);
        return array_map(fn ($row) => [
            'number' => $row->lo,
            'last_draw_date' => $row->last_draw_date,
            'current_gan' => (int) $row->current_gan,
            'max_gan' => (int) $row->max_gan
        ], $rows);
    }
    public function getLoganCap(Category $category, int $month = 1): array
    {
        $prizeCodes = [
            ResultPrizeCodeEnum::G0,
            ResultPrizeCodeEnum::G1,
            ResultPrizeCodeEnum::G2,
            ResultPrizeCodeEnum::G3,
            ResultPrizeCodeEnum::G4,
            ResultPrizeCodeEnum::G5,
            ResultPrizeCodeEnum::G6,
            ResultPrizeCodeEnum::G7,
            ResultPrizeCodeEnum::G8,
        ];

// chuyển enum -> value nếu cần
        $prizeCodeValues = array_map(
            static fn ($e) => $e->value,
            $prizeCodes
        );
        $prizeCodeValues = "'".implode("','", $prizeCodeValues)."'";

        $sql = <<<SQL
WITH
ordered_results AS (
    SELECT
        r.id,
        r.draw_date,
        ROW_NUMBER() OVER (ORDER BY r.draw_date) AS draw_idx
    FROM results r
    WHERE r.category_id = :category_id
      AND r.draw_date >= DATE_SUB(CURDATE(), INTERVAL :month MONTH)
),

lo_hits AS (
    SELECT
        LEAST(RIGHT(rd.number, 2), REVERSE(RIGHT(rd.number, 2)))     AS lo1,
        GREATEST(RIGHT(rd.number, 2), REVERSE(RIGHT(rd.number, 2))) AS lo2,
        o.draw_idx,
        o.draw_date
    FROM ordered_results o
    JOIN result_details rd ON rd.result_id = o.id
    WHERE rd.prize_code IN ($prizeCodeValues)
      AND RIGHT(rd.number, 2) <> REVERSE(RIGHT(rd.number, 2))
),

middle_gaps AS (
    SELECT
        lo1,
        lo2,
        CAST(draw_idx AS SIGNED)
        - CAST(LAG(draw_idx) OVER (PARTITION BY lo1, lo2 ORDER BY draw_idx) AS SIGNED)
        - 1 AS gap
    FROM lo_hits
),

start_gaps AS (
    SELECT
        lo1,
        lo2,
        MIN(draw_idx) - 1 AS gap
    FROM lo_hits
    GROUP BY lo1, lo2
),

current_gan AS (
    SELECT
        lo1,
        lo2,
        MAX(draw_date) AS last_draw_date,
        (SELECT MAX(draw_idx) FROM ordered_results)
        - MAX(draw_idx) AS current_gan
    FROM lo_hits
    GROUP BY lo1, lo2
),

past_max_gan AS (
    SELECT
        lo1,
        lo2,
        MAX(gap) AS past_max_gan
    FROM (
        SELECT lo1, lo2, gap FROM middle_gaps WHERE gap IS NOT NULL
        UNION ALL
        SELECT lo1, lo2, gap FROM start_gaps
    ) t
    GROUP BY lo1, lo2
)

SELECT
    CONCAT(c.lo1, '-', c.lo2) AS lo,
    c.last_draw_date,
    c.current_gan,
    GREATEST(
        COALESCE(p.past_max_gan, 0),
        c.current_gan
    ) AS max_gan
FROM current_gan c
LEFT JOIN past_max_gan p
    ON p.lo1 = c.lo1 AND p.lo2 = c.lo2
ORDER BY c.current_gan DESC
LIMIT 10;
SQL;

        $rows = DB::select($sql, [
            'category_id' => $category->id,
            'month'       => $month,
        ]);
        return array_map(fn ($row) => [
            'number' => $row->lo,
            'last_draw_date' => $row->last_draw_date,
            'current_gan' => (int) $row->current_gan,
            'max_gan' => (int) $row->max_gan
        ], $rows);
    }
    public function getLoganSpecial(Category $category, string $type, int $month = 1): array
    {
        $stringGetLo = match ($type) {
            'head' => 'LEFT(RIGHT(rd.number, 2), 1)',
            'tail' => 'RIGHT(rd.number, 1)',
            'sum' => '(
        CAST(LEFT(RIGHT(rd.number, 2), 1) AS UNSIGNED)
        +
        CAST(RIGHT(rd.number, 1) AS UNSIGNED)
    ) % 10',
            default => throw new \LogicException("$type is not a valid logan type")
        };
        $prizeCodes = "'".ResultPrizeCodeEnum::G0->value."'";

        $sql = <<<SQL
WITH
ordered_results AS (
    SELECT
        r.id,
        r.draw_date,
        ROW_NUMBER() OVER (ORDER BY r.draw_date) AS draw_idx
    FROM results r
    WHERE r.category_id = :category_id
      AND r.draw_date >= DATE_SUB(CURDATE(), INTERVAL :month MONTH)
),
lo_hits AS (
    SELECT
        $stringGetLo AS lo,
        o.draw_idx,
        o.draw_date
    FROM ordered_results o
    JOIN result_details rd ON rd.result_id = o.id
    WHERE rd.prize_code = $prizeCodes
),
middle_gaps AS (
    SELECT
        lo,
        CAST(draw_idx AS SIGNED)
        - CAST(LAG(draw_idx) OVER (PARTITION BY lo ORDER BY draw_idx) AS SIGNED)
        - 1 AS gap
    FROM lo_hits
),
start_gaps AS (
    SELECT
        lo,
        MIN(draw_idx) - 1 AS gap
    FROM lo_hits
    GROUP BY lo
),
current_gan AS (
    SELECT
        lo,
        MAX(draw_date) AS last_draw_date,
        (SELECT MAX(draw_idx) FROM ordered_results)
        - MAX(draw_idx) AS current_gan
    FROM lo_hits
    GROUP BY lo
),
past_max_gan AS (
    SELECT
        lo,
        MAX(gap) AS past_max_gan
    FROM (
        SELECT lo, gap FROM middle_gaps WHERE gap IS NOT NULL
        UNION ALL
        SELECT lo, gap FROM start_gaps
    ) t
    GROUP BY lo
)
SELECT
    c.lo,
    c.last_draw_date,
    c.current_gan,
    GREATEST(
        COALESCE(p.past_max_gan, 0),
        c.current_gan
    ) AS max_gan
FROM current_gan c
LEFT JOIN past_max_gan p ON p.lo = c.lo
ORDER BY c.current_gan DESC
LIMIT 10;
SQL;

        $rows = DB::select($sql, [
            'category_id' => $category->id,
            'month'       => $month,
        ]);
        return array_map(fn ($row) => [
            'number' => $row->lo,
            'last_draw_date' => $row->last_draw_date,
            'current_gan' => (int) $row->current_gan,
            'max_gan' => (int) $row->max_gan
        ], $rows);
    }
    public function getDacbiet(Category $category, int $month = 1): array
    {
        $results = $this->applierList::apply($this->newQuery())
            ->select(['id', 'draw_date'])
            ->where('category_id', $category->id)
            ->where('draw_date', '>=', Carbon::now()->subMonths($month)->toDateString())
            ->with([
                'result_details' => function ($q) {
                    FeSelectListResultDetail::apply($q)->where('prize_code', ResultPrizeCodeEnum::G0);
                }
            ])
            ->orderByDesc('draw_date')
            ->get();

        $list = ResultStatisticSpecialDto::from($results);

        $stats = $this->aggregateLastTwoDigitsStats($list);
        $missing = $this->getMissingLastTwoDigits($list);

        $special = substr($list[0]['number'], -2);
        $sameSpecial = $this->applierList::apply($this->newQuery())
            ->select(['id', 'draw_date'])
            ->where('category_id', $category->id)
            ->whereHas('result_details', function ($q) use ($special) {
                FeSelectListResultDetail::apply($q)
                    ->where('prize_code', ResultPrizeCodeEnum::G0)
                    ->whereRaw('RIGHT(number, 2) = ?', [$special]);
            })
            ->with([
                'result_details' => function ($q) use ($special) {
                    FeSelectListResultDetail::apply($q)
                        ->where('prize_code', ResultPrizeCodeEnum::G0);
                }
            ])
            ->orderByDesc('draw_date')
            ->get();
        $sameSpecial = ResultStatisticSpecialDto::from($sameSpecial);

        return [
            'list' => $list,
            'stats' => array_slice($stats, 0, 10),
            'missing' => $missing,
            'sameSpecial' => $sameSpecial,
            'special' => $special
        ];
    }
    public function getTanSuat(Category $category, int $month = 1)
    {
        $stats = DB::table('results as r')
            ->join('result_details as rd', 'rd.result_id', '=', 'r.id')
            ->where('r.category_id', $category->id)
            ->where('r.draw_date', '>=', now()->subMonths($month))
            ->whereIn('rd.prize_code', ResultPrizeCodeEnum::getPrizeLoto())
            ->selectRaw('RIGHT(rd.number, 2) as last_2, COUNT(*) as count')
            ->groupBy('last_2')
            ->orderByDesc('count')
            ->get();

        $top10 = $stats
            ->take(10)
            ->map(fn ($r) => [
                'number' => $r->last_2,
                'count'  => (int) $r->count,
            ])
            ->values()
            ->toArray();

        $bottom10 = $stats
            ->reverse()   // đảo thứ tự
            ->take(10)
            ->map(fn ($r) => [
                'number' => $r->last_2,
                'count'  => (int) $r->count,
            ])
            ->values()
            ->toArray();

        $all = collect(range(0, 99))
            ->map(fn ($i) => str_pad((string) $i, 2, '0', STR_PAD_LEFT));
        $exists = $stats
            ->pluck('last_2');
        $missing = $all
            ->diff($exists)
            ->values()
            ->toArray();
        return [
            'desc' => $top10,
            'asc' => $bottom10,
            'missing' => $missing
        ];
    }
    public function getLotoStatistic(Category $category, int $month = 1)
    {
        $stats = DB::table('results as r')
            ->join('result_details as rd', 'rd.result_id', '=', 'r.id')
            ->where('r.category_id', $category->id)
            ->where('r.draw_date', '>=', now()->subMonths($month))
            ->whereIn('rd.prize_code', ResultPrizeCodeEnum::getPrizeLoto())
            ->selectRaw('RIGHT(rd.number, 2) as last_2, COUNT(*) as count')
            ->groupBy('last_2')
            ->orderByDesc('count')
            ->get();
        $total = $stats->sum('count');
        $max = $stats->max('count');

        $list = $stats->map(fn ($row) => [
            'number'  => $row->last_2,
            'count'   => $row->count,
            'percent' => $total > 0 ?  round($row->count / $total * 100, 2) : 0,
        ])->toArray();
        usort($list, static function ($a, $b) {
            return (int) $a['number'] <=> (int) $b['number'];
        });

        return [
            'list' => $list,
            'max' => $max,
        ];
    }
    public function getLokepCap(Category $category, int $month = 1)
    {
        $stats = DB::select("WITH lo_kep_by_date AS (
    SELECT DISTINCT
        r.draw_date,
        RIGHT(rd.number, 2) AS lo
    FROM results r
    JOIN result_details rd ON rd.result_id = r.id
    WHERE r.category_id = $category->id
      AND r.draw_date >= DATE_SUB(CURDATE(), INTERVAL $month MONTH)
      AND RIGHT(rd.number, 2) IN (
          '00','11','22','33','44','55','66','77','88','99'
      )
),
lo_kep_pair_by_date AS (
    SELECT
        a.draw_date,
        CONCAT(a.lo, '-', b.lo) AS lo_kep_pair
    FROM lo_kep_by_date a
    JOIN lo_kep_by_date b
        ON a.draw_date = b.draw_date
       AND CAST(a.lo AS UNSIGNED) < CAST(b.lo AS UNSIGNED)
),
ordered_pairs AS (
    SELECT
        lo_kep_pair,
        draw_date
    FROM lo_kep_pair_by_date
    ORDER BY draw_date ASC
)
SELECT
    lo_kep_pair,
    COUNT(*) AS total_days,
    JSON_ARRAYAGG(draw_date) AS draw_dates
FROM (
    SELECT
        lo_kep_pair,
        draw_date
    FROM lo_kep_pair_by_date
    ORDER BY draw_date ASC
    LIMIT 18446744073709551615
) t
GROUP BY lo_kep_pair
ORDER BY total_days DESC, lo_kep_pair
LIMIT 20;
");
        $stats = array_map(function ($i){
            return [
                'number' => $i->lo_kep_pair,
                'count'  => $i->total_days,
                'draw_dates' => json_decode($i->draw_dates, true)
            ];
        }, $stats);

        return $stats;
    }
    private function aggregateLastTwoDigitsStats(array $rows): array
    {
        $stats = [];

        foreach ($rows as $row) {
            $lastTwo = substr($row['number'], -2);
            $drawDate = $row['draw_date'];

            if (!isset($stats[$lastTwo])) {
                $stats[$lastTwo] = [
                    'number'   => $lastTwo,
                    'count'      => 1,
                    'latest_draw_date'=> $drawDate,
                ];
                continue;
            }

            $stats[$lastTwo]['count']++;

            if ($drawDate > $stats[$lastTwo]['latest_draw_date']) {
                $stats[$lastTwo]['latest_draw_date'] = $drawDate;
            }
        }

        usort($stats, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return array_values($stats);
    }
    private function getMissingLastTwoDigits(array $rows): array
    {
        // full set 00 -> 99
        $all = [];

        for ($i = 0; $i <= 99; $i++) {
            $all[] = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        }

        // extract existing last-two digits
        $exists = [];

        foreach ($rows as $row) {
            $exists[] = substr($row['number'], -2);
        }

        // unique để khỏi tốn não
        $exists = array_unique($exists);

        // diff
        return array_values(array_diff($all, $exists));
    }

    public function formatDataForCalendar(array $data): array
    {
        // index dữ liệu theo draw_date cho dễ tra
        $map = [];
        foreach ($data as $item) {
            if (isset($item['draw_date'])) {
                $map[$item['draw_date']] = $item;
            }
        }

        $dates = array_keys($map);
        sort($dates);

        $start = Carbon::parse($dates[0])->startOfWeek(Carbon::MONDAY);
        $end   = Carbon::parse(end($dates))->endOfWeek(Carbon::SUNDAY);

        $result = [];
        $week = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {

            $key = $date->format('Y-m-d');

            $week[] = $map[$key] ?? null;

            // đủ 7 ngày (T2 -> CN)
            if (count($week) === 7) {
                $result[] = $week;
                $week = [];
            }
        }
        return $result;
    }

    public function getFullDataFeLogan(string $code, int $month): array
    {
        $code = strtoupper($code);
        $categories = resolve(CategoryService::class)->statisticCategories();
        $category = $categories->first(fn($i) => $i->code == $code);

        $months = [1,3,6];
        if (! in_array($month, $months))
            throw new \RuntimeException("Khoảng thời gian không phù hợp");

        $logan = $this->getLogan($category, $month);
        $loganCap = $this->getLoganCap($category, $month);
        $loganSpecialHead = $this->getLoganSpecial($category, 'head', $month);
        $loganSpecialTail = $this->getLoganSpecial($category, 'tail', $month);
        $loganSpecialSum = $this->getLoganSpecial($category, 'sum', $month);

        return [
            'logan' => $logan,
            'loganCap' => $loganCap,
            'loganSpecialHead' => $loganSpecialHead,
            'loganSpecialTail' => $loganSpecialTail,
            'loganSpecialSum' => $loganSpecialSum,
            'categories' => CategorySelectOptionDto::from($categories),
            'category' => CategorySelectOptionDto::from($category),
            'months' => $months,
            'month' => $month,
        ];
    }

    public function lookUp(Category $category, string $number): Collection
    {
        $number = trim($number);

        // 1. Lấy category_id
        if ($category->code === 'XSMB') {
            $categoryIds = [$category->id];
        } else {
            $categoryIds = $category->children()->pluck('id')->toArray();
        }

        if (empty($categoryIds)) {
            return collect();
        }

        $fromDate = Carbon::now()->subDays(30)->toDateString();

        $isMB = $category->code === 'XSMB';

        // 2. Mapping độ dài số lưu trong DB
        $lengthMap = $isMB
            ? [
                ResultPrizeCodeEnum::G0->value => 5,
                ResultPrizeCodeEnum::G1->value => 5,
                ResultPrizeCodeEnum::G2->value => 5,
                ResultPrizeCodeEnum::G3->value => 5,
                ResultPrizeCodeEnum::G4->value => 4,
                ResultPrizeCodeEnum::G5->value => 4,
                ResultPrizeCodeEnum::G6->value => 3,
                ResultPrizeCodeEnum::G7->value => 2,
            ]
            : [
                ResultPrizeCodeEnum::G0->value => 6,
                ResultPrizeCodeEnum::G1->value => 5,
                ResultPrizeCodeEnum::G2->value => 5,
                ResultPrizeCodeEnum::G3->value => 5,
                ResultPrizeCodeEnum::G4->value => 5,
                ResultPrizeCodeEnum::G5->value => 4,
                ResultPrizeCodeEnum::G6->value => 4,
                ResultPrizeCodeEnum::G7->value => 3,
                ResultPrizeCodeEnum::G8->value => 2,
            ];

        return $this->applierList::apply($this->newQuery())
            ->whereIn('results.category_id', $categoryIds)
            ->whereDate('results.draw_date', '>=', $fromDate)
            ->whereHas('result_details', function ($query) use ($lengthMap, $number) {

                $query->where(function ($q) use ($lengthMap, $number) {

                    foreach ($lengthMap as $prizeCode => $length) {
                        $tail = substr($number, -$length);

                        $q->orWhere(function ($sub) use ($prizeCode, $tail) {
                            $sub->where('prize_code', $prizeCode)
                                ->where('number', $tail);
                        });
                    }

                });

            })
            ->with([
                'result_details' => function ($q) use ($lengthMap, $number) {

                    $q->where(function ($subQ) use ($lengthMap, $number) {

                        foreach ($lengthMap as $prizeCode => $length) {
                            $tail = substr($number, -$length);

                            $subQ->orWhere(function ($sub) use ($prizeCode, $tail) {
                                $sub->where('prize_code', $prizeCode)
                                    ->where('number', $tail);
                            });
                        }

                    });

                },
                'category' => fn($q) => FeSelectListCategory::apply($q)
            ])
            ->orderByDesc('results.draw_date')
            ->get();
    }

    public function getDataStatisticSpecialByYear(int $year): array
    {
        $category = resolve(CategoryService::class)
            ->todayCategories()
            ->first(fn($i) => $i->code === 'XSMB');

        if (!$category) {
            return [];
        }

        $results = $this->applierList::apply($this->newQuery())
            ->where('category_id', $category->id)
            ->whereYear('draw_date', $year)
            ->with([
                'result_details' => fn($q) => $q
                    ->select(['result_id', 'number'])
                    ->where('prize_code', ResultPrizeCodeEnum::G0)
            ])
            ->get();

        // ===== 1. LIST 31 × 12 =====
        $list = array_fill(1, 31, array_fill(1, 12, null));

        // ===== 2. Khởi tạo thống kê lô tô 00–99 =====
        $lotoCounts = [];
        for ($i = 0; $i <= 99; $i++) {
            $lotoCounts[str_pad((string)$i, 2, '0', STR_PAD_LEFT)] = 0;
        }

        $even = 0;
        $odd = 0;

        foreach ($results as $result) {
            if ($result->result_details->isEmpty()) {
                continue;
            }

            $day = (int) $result->draw_date->day;
            $month = (int) $result->draw_date->month;

            $numberFull = $result->result_details->first()->number;
            $list[$day][$month] = $numberFull;

            // Lô tô = 2 số cuối
            $loto = substr($numberFull, -2);

            $lotoCounts[$loto]++;

            if (((int)$loto % 2) === 0) {
                $even++;
            } else {
                $odd++;
            }
        }

        // ===== 3. MIN / MAX =====
        $maxCount = max($lotoCounts);
        $minCount = min($lotoCounts);

        $maxNumbers = [];
        $minNumbers = [];

        foreach ($lotoCounts as $number => $count) {
            if ($count === $maxCount) {
                $maxNumbers[] = $number;
            }
            if ($count === $minCount) {
                $minNumbers[] = $number;
            }
        }

        // ===== 4. SUM MAX =====
        $sumMaxValue = -1;
        $sumMaxNumber = null;

        $sumCount = [];
        foreach ($lotoCounts as $number => $count) {
            $sum = (substr($number, 0, 1) + substr($number, -1)) % 10;
            $sumCount[$sum] = ($sumCount[$sum] ?? 0) + $count;
        }
        $maxSumCount = max($sumCount);
        $indexMaxSumCount = array_keys($sumCount, $maxSumCount)[0];

        return [
            'list' => $list,
            'statistic' => [
                'min' => [
                    'count' => $minCount,
                    'numbers' => $minNumbers,
                ],
                'max' => [
                    'count' => $maxCount,
                    'numbers' => $maxNumbers,
                ],
                'even' => $even,
                'odd' => $odd,
                'sum_max' => [
                    'count' => $maxSumCount,
                    'number' => $indexMaxSumCount,
                ],
            ],
        ];
    }

    public function getDataStatisticSpecialByMonth(int $startYear, int $endYear): array
    {
        $category = resolve(CategoryService::class)
            ->todayCategories()
            ->first(fn($i) => $i->code === 'XSMB');

        if (!$category) {
            return [];
        }

        $results = $this->applierList::apply($this->newQuery())
            ->where('category_id', $category->id)
            ->whereBetween('draw_date', [
                $startYear . '-01-01',
                $endYear . '-12-31'
            ])
            ->with([
                'result_details' => fn($q) => $q
                    ->select(['result_id', 'number'])
                    ->where('prize_code', ResultPrizeCodeEnum::G0)
            ])
            ->get();

        // ================== STATISTIC END YEAR ==================
        $statNumbers = [];

        foreach ($results as $result) {

            $year = Carbon::parse($result->draw_date)->year;

            if ($year !== $endYear) {
                continue;
            }

            foreach ($result->result_details as $detail) {
                $number = $detail->number;

                // Lấy 2 số cuối (lô tô)
                $loto = substr($number, -2);

                $statNumbers[] = $loto;
            }
        }

// Đếm tổng loto 00 -> 99
        $lotoCount = array_count_values($statNumbers);

// Khởi tạo 0 -> 9
        $sumLoto  = array_fill(0, 10, 0);
        $headLoto = array_fill(0, 10, 0);
        $tailLoto = array_fill(0, 10, 0);

        foreach ($lotoCount as $loto => $count) {
            $head = (int) substr($loto, 0, 1);
            $tail = (int) substr($loto, -1);
            $sum = ($head + $tail) % 10;

            $sumLoto[$sum]  += $count;
            $headLoto[$head] += $count;
            $tailLoto[$tail] += $count;
        }

// Tìm về nhiều / về ít
        // ================== COUNT FULL 00 -> 99 ==================

        $fullLotoCount = [];

        for ($i = 0; $i <= 99; $i++) {
            $key = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            $fullLotoCount[$key] = 0;
        }

// Merge số đã về
        foreach ($lotoCount as $loto => $count) {
            $fullLotoCount[$loto] = $count;
        }

// ================== VỀ NHIỀU ==================

        $desc = $fullLotoCount;
        arsort($desc);

// Lấy top 10
        $veNhieu = array_slice($desc, 0, 10, true);

// ================== VỀ ÍT ==================

        $asc = $fullLotoCount;
        asort($asc);

// Lấy 10 loto ít nhất
        $veIt = array_slice($asc, 0, 10, true);

        // Map theo ngày
        $mapByDate = [];

        foreach ($results as $result) {
            $detail = $result->result_details->first();
            if (!$detail) continue;

            $date = Carbon::parse($result->draw_date)->format('Y-m-d');

            $mapByDate[$date] = [
                'draw_date' => $date,
                'number'    => $detail->number,
            ];
        }

        $list = [];

        for ($year = $startYear; $year <= $endYear; $year++) {

            $startOfYear = Carbon::create($year, 1, 1);
            $endOfYear   = Carbon::create($year, 12, 31);

            $current = $startOfYear->copy();
            $weekIndex = 1;

            $list[$year][$weekIndex] = [];

            // Pad đầu tuần nếu 1/1 không phải thứ 2
            $firstWeekday = $startOfYear->dayOfWeekIso; // 1 = Mon

            if ($firstWeekday !== 1) {
                for ($i = 1; $i < $firstWeekday; $i++) {
                    $list[$year][$weekIndex][] = null;
                }
            }

            while ($current->lte($endOfYear)) {

                $dateStr = $current->format('Y-m-d');

                $list[$year][$weekIndex][] = $mapByDate[$dateStr] ?? null;

                if ($current->dayOfWeekIso === 7) {

                    while (count($list[$year][$weekIndex]) < 7) {
                        $list[$year][$weekIndex][] = null;
                    }

                    $weekIndex++;

                    if ($current->lt($endOfYear)) {
                        $list[$year][$weekIndex] = [];
                    }
                }

                $current->addDay();
            }

            // Pad tuần cuối nếu chưa đủ 7
            if (!empty($list[$year][$weekIndex]) && count($list[$year][$weekIndex]) < 7) {
                while (count($list[$year][$weekIndex]) < 7) {
                    $list[$year][$weekIndex][] = null;
                }
            }

            // Week giảm dần
            ksort($list[$year]);
        }

        return [
            'data'      => $list,
            'veNhieu'   => $veNhieu ?? null,
            'veIt'      => $veIt ?? null,
            'sumLoto'   => $sumLoto,
            'headLoto'  => $headLoto,
            'tailLoto'  => $tailLoto,
        ];
    }

    public function getDataStatisticByDays(int $limit): array
    {
        $category = resolve(CategoryService::class)
            ->todayCategories()
            ->first(fn($i) => $i->code === 'XSMB');

        if (!$category) {
            return [];
        }

        $results = $this->applierList::apply($this->newQuery())
            ->where('category_id', $category->id)
            ->limit($limit)
            ->orderByDesc('draw_date')
            ->with([
                'result_details' => fn($q) => $q
                    ->select(['result_id', 'prize_code', 'number'])
                    ->whereNot('prize_code', ResultPrizeCodeEnum::Madb)
            ])
            ->get();

        // =========================
        // INIT DATA
        // =========================
        $dataLoto = [
            'list' => [],
            'headLoto' => [],
            'tailLoto' => [],
            'sumLoto' => [],
        ];

        $dataSpecial = [
            'list' => [],
            'headLoto' => [],
            'tailLoto' => [],
            'sumLoto' => [],
        ];

        foreach ($results as $result) {
            foreach ($result->result_details as $detail) {

                $number = (string) $detail->number;

                $loto = substr($number, -2);
                $head = substr($loto, 0, 1);
                $tail = substr($loto, -1);
                $sum  = ((int)$head + (int)$tail) % 10;

                // =========================
                // ALL LOTO
                // =========================
                $dataLoto['list'][$loto] = ($dataLoto['list'][$loto] ?? 0) + 1;
                $dataLoto['headLoto'][$head] = ($dataLoto['headLoto'][$head] ?? 0) + 1;
                $dataLoto['tailLoto'][$tail] = ($dataLoto['tailLoto'][$tail] ?? 0) + 1;
                $dataLoto['sumLoto'][(string)$sum] = ($dataLoto['sumLoto'][(string)$sum] ?? 0) + 1;

                // =========================
                // SPECIAL (G0 ONLY)
                // =========================
                if ($detail->prize_code == ResultPrizeCodeEnum::G0->value) {

                    $dataSpecial['list'][$loto] = ($dataSpecial['list'][$loto] ?? 0) + 1;
                    $dataSpecial['headLoto'][$head] = ($dataSpecial['headLoto'][$head] ?? 0) + 1;
                    $dataSpecial['tailLoto'][$tail] = ($dataSpecial['tailLoto'][$tail] ?? 0) + 1;
                    $dataSpecial['sumLoto'][(string)$sum] = ($dataSpecial['sumLoto'][(string)$sum] ?? 0) + 1;
                }
            }
        }

        // =========================
        // NORMALIZE + SORT
        // =========================
        $normalize = function (array &$data): void {

            // list 00 -> 99
            for ($i = 0; $i <= 99; $i++) {
                $key = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
                $data['list'][$key] = $data['list'][$key] ?? 0;
            }

            // head, tail, sum 0 -> 9
            for ($i = 0; $i <= 9; $i++) {
                $key = (string)$i;
                $data['headLoto'][$key] = $data['headLoto'][$key] ?? 0;
                $data['tailLoto'][$key] = $data['tailLoto'][$key] ?? 0;
                $data['sumLoto'][$key] = $data['sumLoto'][$key] ?? 0;
            }

            // sort
            arsort($data['list']);      // value giảm dần
            ksort($data['headLoto']);   // 0 -> 9
            ksort($data['tailLoto']);   // 0 -> 9
            ksort($data['sumLoto']);    // 0 -> 9
        };

        $normalize($dataLoto);
        $normalize($dataSpecial);

        return [
            'dataLoto' => $dataLoto,
            'dataSpecial' => $dataSpecial,
        ];
    }

    public function getSitemapIndexData(int $perPage = 500): array
    {
        $rows = $this->newQuery()
            ->selectRaw('YEAR(draw_date) as year, MAX(draw_date) as lastmod')
            ->groupByRaw('YEAR(draw_date)')
            ->orderByDesc('year')
            ->get();

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'loc' => $row->year,      // sitemap-results-2026.xml
                'lastmod' => \Illuminate\Support\Carbon::create($row->lastmod),
            ];
        }

        return $data;
    }
}

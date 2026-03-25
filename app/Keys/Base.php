<?php

namespace App\Keys;

use App\Helpers\ResultHelper;
use App\Models\Soicau;
use App\Services\CrawlerServices\AtrungroiService;
use Illuminate\Support\Carbon;

abstract class Base
{
    protected ?int $id = null;
    protected array $number = [];
    protected string $key = '';

    protected ?Carbon $start_date = null;
    protected int $range_day = 1;

    protected ?array $number_win = null;
    protected ?int $win_day = null;

    protected bool $is_active = true;

    protected Soicau $soicauModel;
    protected AtrungroiService $resultService;

    protected bool $checkOnlySpecial = false;

    protected string $type;

    public function __construct()
    {
        $this->soicauModel = resolve(Soicau::class);
        $this->resultService = resolve(AtrungroiService::class);
    }

    public function init(): void
    {
        $object = $this->soicauModel->getLatestSoiCau($this->key);

        if (!$object) {
            $this->createNew();
            return;
        }

        $this->id = $object->id;
        $this->number = $object->number;
        $this->key = $object->key;

        $this->start_date = Carbon::parse($object->start_date);
        $this->range_day = (int)$object->range_day;

        $this->number_win = $object->number_win;
        $this->win_day = $object->win_day;
        $this->is_active = $object->is_active;

        $this->updateOld();
        $this->createNew();
    }

    abstract protected function setNewNumber(): void;

    protected function logicChangeResult(array $loto = []): bool
    {
        $loto = ResultHelper::getLoto($loto);
        if ($this->checkOnlySpecial) {
            $loto = array_slice($loto, 0, 1);
        }

        $result = array_intersect($this->number, $loto);

        if ($result) {
            $this->number_win = array_count_values($result);
            return true;
        }

        return false;
    }

    protected function logicChangeWinDate(?string $win_date = null): void
    {
        if (!$win_date) {
            $this->win_day = 0;
            return;
        }

        $winDate = Carbon::parse($win_date);

        $this->win_day = $this->start_date->diffInDays($winDate) + 1;
    }

    protected function logicChangeIsActive(): void
    {
        // ví dụ: hết range thì inactive
        $endDate = $this->getEndDate();

        if ($endDate->lt(Carbon::today())) {
            $this->is_active = 0;
        }
    }

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'key' => $this->key,
            'start_date' => $this->start_date?->toDateString(),
            'range_day' => $this->range_day,
            'number_win' => $this->number_win,
            'win_day' => $this->win_day,
            'is_active' => $this->is_active,
        ];
    }

    protected function getEndDate(): Carbon
    {
        return $this->start_date
            ->copy()
            ->addDays($this->range_day - 1);
    }


    protected function createNew(): bool
    {
        if (!$this->start_date) {
            $start_date = Carbon::today();
        } else {
            $start_date = $this->start_date->copy()->addDays(
                ($this->win_day && $this->win_day > 0)
                    ? $this->win_day
                    : $this->range_day
            );
        }

        if ($start_date->gt(Carbon::today())) {
            return false;
        }

        $this->start_date = $start_date;

        $this->setNewNumber();

        $this->number_win = null;
        $this->win_day = null;
        $soicau = $this->soicauModel->create($this->toArray());

        return $soicau->exists;
    }

    public function updateOld(): void
    {
        $results = $this->getResult();
        $endDate = $this->getEndDate()->toDateString();

        foreach ($results as $date => $result) {
            if ($this->logicChangeResult($result)) {
                $this->logicChangeWinDate($date);
                break;
            }

            if ($date === $endDate) {
                $this->logicChangeWinDate(null);
                break;
            }
        }

        $this->logicChangeIsActive();

        $this->soicauModel->updateSoiCau($this->id, $this->toArray());
    }

    private function getResult(): array
    {
        $tmp = [];

        $start = $this->start_date->copy()->startOfDay();
        $end = $this->getEndDate()->copy()->endOfDay();

        $results = $this->resultService->getResultXsmb();

        foreach ($results as $item) {
            /** @var \Illuminate\Support\Carbon $drawDate */
            $drawDate = $item['draw_date'];

            if ($drawDate->between($start, $end)) {
                $dateKey = $drawDate->toDateString();
                $tmp[$dateKey] = $item['results'];
            }
        }

        ksort($tmp); // đảm bảo đúng thứ tự ngày

        return $tmp;
    }
}

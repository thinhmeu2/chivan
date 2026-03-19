<?php
namespace App\Keys;

use App\Models\Result;
use App\Models\Soicau;

abstract class Base
{
    protected $id;
    protected $number;
    protected $type;
    protected $start_date;
    protected $end_date;
    protected $result = null;
    protected $win_date = null;
    protected $is_active = 1;

    /** @var Soicau */
    protected $soicauModel;

    /** @var Result */
    protected $resultModel;
    protected $checkOnlySpecial = 0;
    protected $rangeDate = 1;

    public function __construct()
    {
        $this->soicauModel = new \Soicau_model();
        $this->resultModel = new \Result_model();
    }

    public function init(): void
    {
        $objectSoiCau = $this->soicauModel->getLatestSoiCauV2($this->type);

        if (!$objectSoiCau) {
            $this->createNew();
        } else {
            $this->id = $objectSoiCau->id;
            $this->number = json_decode($objectSoiCau->number, true);
            $this->type = $objectSoiCau->type;
            $this->start_date = \DateTime::createFromFormat('Y-m-d', $objectSoiCau->start_date);
            $this->end_date = \DateTime::createFromFormat('Y-m-d', $objectSoiCau->end_date);
            $this->result = json_decode($objectSoiCau->result, true);
            $this->win_date = $objectSoiCau->win_date;
            $this->is_active = $objectSoiCau->is_active;

            $this->updateOld();
            $this->createNew();
        }
    }

    abstract protected function setNewNumber(): void;

    protected function logicChangeResult(array $loto = []): bool
    {
        if ($this->checkOnlySpecial)
            $loto = array_splice($loto, 0, 1);
        $result = array_intersect($this->number, $loto);

        if ($result) {
            $this->result = json_encode(array_count_values($result));
            return true;
        }

        return false;
    }

    protected function logicChangeWinDate(string $win_date = null): void
    {
        if (! $win_date){
            $this->win_date = 0;
        } else {
            $winDate = new \DateTime($win_date);
            $this->win_date = $this->start_date->diff($winDate)->days + 1;
        }
    }

    protected function logicChangeIsActive(): void
    {
        if (!defined('TIME_NGHI_TET') || empty(TIME_NGHI_TET)) {
            return;
        }

        $tetStart = new \DateTime(TIME_NGHI_TET[0]);
        $tetEnd   = new \DateTime(TIME_NGHI_TET[1]);

        $start = $this->start_date instanceof \DateTime
            ? $this->start_date
            : new \DateTime($this->start_date);

        $end = $this->end_date instanceof \DateTime
            ? $this->end_date
            : new \DateTime($this->end_date);

        if ($start >= $tetStart && $end <= $tetEnd) {
            $this->is_active = 0;
        }
    }

    public function toArray(): array
    {
        return [
            'number' => json_encode($this->number),
            'type' => $this->type,
            'start_date' => $this->start_date->format('Y-m-d'),
            'end_date' => $this->end_date->format('Y-m-d'),
            'result' => $this->result,
            'win_date' => $this->win_date,
            'is_active' => $this->is_active,
        ];
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function createNew(): bool
    {
        // Nếu chưa có dữ liệu trước đó (init lần đầu)
        if (!$this->start_date) {
            $start_date = new \DateTime();
        } else {
            if ($this->win_date > 0) {
                // Có ngày thắng → nhảy theo win_date
                $start_date = clone $this->start_date;
                $start_date->add(new \DateInterval('P' . $this->win_date . 'D'));
            } else {
                // Không thắng → lấy tiếp sau end_date
                $start_date = clone $this->end_date;
                $start_date->add(new \DateInterval('P1D'));
            }
        }

        // Chặn tương lai (tránh insert rác)
        if ($start_date->format('Y-m-d') > DATE_TODAY_MB) {
            return false;
        }

        $this->start_date = $start_date;

        $realRangeDay = $this->rangeDate - 1;

        $this->end_date = (clone $start_date)
            ->add(new \DateInterval('P' . $realRangeDay . 'D'));

        $this->setNewNumber();
        $this->result = null;
        $this->win_date = null;

        return $this->soicauModel->insert($this->toArray());
    }

    public function updateOld(): void
    {
        $results = $this->getResult();
        foreach ($results as $displayed_time => $loto){
            if ($this->logicChangeResult($loto)){
                $this->logicChangeWinDate($displayed_time);
                break;
            } elseif ($displayed_time == $this->end_date->format('Y-m-d')) {
                $this->logicChangeWinDate(0);
                break;
            }
        }

        $this->logicChangeIsActive();
        $this->soicauModel->updateSoiCau($this->id, $this->toArray());
    }

    private function getResult(): array
    {
        $tmp = [];

        $period = new \DatePeriod(
            $this->start_date,
            new \DateInterval('P1D'),
            (clone $this->end_date)->modify('+1 day')
        );

        foreach ($period as $date) {
            $day = $date->format('Y-m-d');

            $results = $this->resultModel->getFromDayToDay(
                1,
                $day,
                $day
            );

            foreach ($results as $i) {
                $tmp[$i['displayed_time']] = getLoto($i['data_result'], 'loto');
            }
        }

        return $tmp;
    }
}

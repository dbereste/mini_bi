<?php

namespace Modules\Dashboard\Overview\Services;

use App\Models\DepositTransaction;
use App\Models\Player;
use App\Models\PlayerDailyActivity;

class MetricsService
{
    /**
     * Sum of approved deposits (EUR) in the period.
     */
    public function totalDeposits(string $from, string $to): float
    {
        return $this->approvedDeposits($from, $to)->sum();
    }

    /**
     * Number of approved deposits in the period.
     */
    public function depositCount(string $from, string $to): int
    {
        return $this->approvedDeposits($from, $to)->count();
    }

    /**
     * Average approved deposit (EUR) in the period.
     */
    public function averageDeposit(string $from, string $to): float
    {
        if ($this->depositCount($from, $to) === 0) {
            return 0;
        }

        return $this->totalDeposits($from, $to) / $this->depositCount($from, $to);
    }

    /**
     * Players with at least one activity row in the period.
     */
    public function activePlayers(string $from, string $to): int
    {
        return PlayerDailyActivity::whereBetween('reporting_date', [$from, $to])
            ->pluck('player_idt')
            ->unique()
            ->count();
    }

    public function registrations(string $from, string $to): int
    {
        return Player::whereBetween('created', [$from, $to])->count();
    }

    public function firstTimeDepositors(string $from, string $to): int
    {
        return Player::whereBetween('first_deposit_date', [$from, $to])->count();
    }

    /**
     * FTD conversion rate, in percent.
     */
    public function ftdConversion(string $from, string $to): float
    {
        return round($this->firstTimeDepositors($from, $to) / $this->registrations($from, $to) * 100, 2);
    }

    private function approvedDeposits(string $from, string $to)
    {
        return DepositTransaction::whereRaw('date(created) between ? and ?', [$from, $to])
            ->where('status', 'approved')
            ->pluck('amount_eur');
    }
}

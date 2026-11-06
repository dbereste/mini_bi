<?php

namespace Modules\Dashboard\Overview\Repositories;

use App\Models\Affiliate;

class OverviewRepository
{
    /**
     * Affiliate performance for the period: depositors, deposits and NGR per affiliate.
     */
    public function affiliatePerformance(
        string $from,
        string $to,
        ?string $search = null,
        string $sort = 'deposits',
        string $dir = 'desc',
        ?int $limit = null
    ) {
        $query = Affiliate::from('dw_affiliates as a')
            ->select('a.*')
            ->selectRaw('COUNT(DISTINCT d.player_idt) as depositors')
            ->selectRaw('COALESCE(SUM(d.amount_eur), 0) as deposits')
            ->selectRaw('COALESCE(SUM(da.ngr), 0) as ngr')
            ->leftJoin('dw_players as p', 'p.affiliate_id', '=', 'a.affiliate_id')
            ->leftJoin('dw_deposit_transactions as d', function ($join) use ($from, $to) {
                $join->on('d.player_idt', '=', 'p.player_idt')
                    ->whereRaw('date(d.created) between ? and ?', [$from, $to])
                    ->whereRaw("lower(d.status) = 'approved'");
            })
            ->leftJoin('dw_player_daily_activity as da', function ($join) use ($from, $to) {
                $join->on('da.player_idt', '=', 'p.player_idt')
                    ->whereBetween('da.reporting_date', [$from, $to]);
            })
            ->where('p.is_test', 0)
            ->groupBy('a.id');

        if ($search) {
            $query->where('a.name', 'like', '%' . $search . '%');
        }

        $query->orderByRaw($sort . ' ' . $dir);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}

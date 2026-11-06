<?php

namespace Modules\Dashboard\Overview\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Dashboard\Overview\Repositories\OverviewRepository;
use Modules\Dashboard\Overview\Services\MetricsService;

class OverviewController extends Controller
{
    public function __construct(
        private MetricsService $metrics,
        private OverviewRepository $repository
    ) {
    }

    public function index(Request $request)
    {
        $from = $request->input('from', config('dashboard.default_from'));
        $to = $request->input('to', config('dashboard.default_to'));
        $sort = $request->input('sort', 'deposits');
        $dir = $request->input('dir', 'desc');
        $search = $request->input('q');

        $cards = [
            'deposits' => $this->metrics->totalDeposits($from, $to),
            'deposit_count' => $this->metrics->depositCount($from, $to),
            'avg_deposit' => $this->metrics->averageDeposit($from, $to),
            'active_players' => $this->metrics->activePlayers($from, $to),
            'registrations' => $this->metrics->registrations($from, $to),
            'ftds' => $this->metrics->firstTimeDepositors($from, $to),
            'conversion' => $this->metrics->ftdConversion($from, $to),
        ];

        $affiliates = $this->repository->affiliatePerformance(
            $from, $to, $search, $sort, $dir, config('dashboard.table_limit')
        );

        return view('overview::index', compact('cards', 'affiliates', 'from', 'to', 'sort', 'dir', 'search'));
    }

    public function export(Request $request)
    {
        $from = $request->input('from', config('dashboard.default_from'));
        $to = $request->input('to', config('dashboard.default_to'));

        $affiliates = $this->repository->affiliatePerformance($from, $to, $request->input('q'));

        $csv = "Affiliate ID,Affiliate,Brand,Registrations,FTDs,Depositors,Deposits EUR,NGR EUR\n";
        foreach ($affiliates as $affiliate) {
            $csv .= implode(',', [
                $affiliate->affiliate_id,
                $affiliate->name,
                $affiliate->brand->name ?? '',
                $affiliate->players()->count(),
                $affiliate->players()->whereBetween('first_deposit_date', [$from, $to])->count(),
                $affiliate->depositors,
                $affiliate->deposits,
                $affiliate->ngr,
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="affiliates_' . $from . '_' . $to . '.csv"',
        ]);
    }
}

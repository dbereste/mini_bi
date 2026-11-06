<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncDailyActivity extends Command
{
    protected $signature = 'bi:sync-daily-activity {date? : Reporting date (YYYY-MM-DD), defaults to yesterday}';

    protected $description = 'Builds dw_player_daily_activity rows for one reporting date from the transaction tables';

    public function handle(): int
    {
        $date = $this->argument('date') ?? now()->subDay()->toDateString();

        $this->info("Syncing daily activity for {$date}...");

        $deposits = DB::table('dw_deposit_transactions')
            ->selectRaw('player_idt, domain, SUM(amount_eur) as amount, COUNT(*) as cnt')
            ->whereRaw('date(created) = ?', [$date])
            ->whereRaw("lower(status) = 'approved'")
            ->groupBy('player_idt', 'domain')
            ->get();

        $withdrawals = DB::table('dw_withdraw_transactions')
            ->selectRaw('player_idt, SUM(amount_eur) as amount, COUNT(*) as cnt')
            ->whereRaw('date(created) = ?', [$date])
            ->where('status', 'approved')
            ->groupBy('player_idt')
            ->get()
            ->keyBy('player_idt');

        foreach ($deposits as $row) {
            $wd = $withdrawals->get($row->player_idt);

            DB::table('dw_player_daily_activity')->insert([
                'player_idt' => $row->player_idt,
                'domain' => $row->domain,
                'reporting_date' => $date,
                'deposit_amount_eur' => round($row->amount, 2),
                'deposit_count' => $row->cnt,
                'withdraw_amount_eur' => $wd ? round($wd->amount, 2) : 0,
                'withdraw_count' => $wd ? $wd->cnt : 0,
                'ggr_casino_eur' => 0,
                'ggr_sport_eur' => 0,
                'bonus_eur' => 0,
                'ngr' => 0,
                'rec_ins_date' => now()->toDateTimeString(),
            ]);
        }

        $this->info('Inserted ' . count($deposits) . ' rows.');

        return self::SUCCESS;
    }
}

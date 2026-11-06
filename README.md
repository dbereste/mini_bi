# Mini BI

A small Laravel 10 dashboard on top of a SQLite "warehouse" of synthetic iGaming data
(brands, affiliates, players, deposits, withdrawals and daily player activity, about 4.4M rows in total).

Your assignment is in **[TASKS.md](TASKS.md)**.

## Running it

You need PHP 8.1+ (with the `pdo_sqlite` extension) and Composer.

The SQLite database (about 440 MB) is not in the repository. Download it from
the [Releases page](https://github.com/dbereste/mini_bi/releases) and put it at
`database/database.sqlite`, or generate it yourself (Python 3, takes 2-3 minutes):
`python3 scripts/generate_data.py database/database.sqlite`.

```bash
git clone https://github.com/dbereste/mini_bi.git
cd mini_bi
composer install
cp .env.example .env
# place database/database.sqlite here (see above)
php artisan serve
```

Open http://localhost:8000. The first page load is slow, and that is part of the assignment.

Some queries need more memory than PHP's default 128M; set `memory_limit = 512M` in your `php.ini`.

## Useful commands

```bash
php artisan migrate                      # run your migrations
php artisan test                         # run tests
sqlite3 database/database.sqlite         # SQL shell
tail -f storage/logs/laravel.log

python3 scripts/generate_data.py database/database.sqlite   # reset the database (regenerate from scratch)
```

## Layout

```
Modules/Dashboard/
  Providers/DashboardServiceProvider.php    registers views and routes
  Overview/
    Http/Controllers/OverviewController.php
    Repositories/OverviewRepository.php
    Services/MetricsService.php
    Resources/views/                         index + partials (cards, table)
    routes/web.php
app/Models/                                  Eloquent models for the dw_* tables
app/Console/Commands/SyncDailyActivity.php   nightly job that builds dw_player_daily_activity
database/migrations/                         schema of the dw_* tables
database/schema/schema.sql                   the same schema as plain SQL (used by the data generator)
scripts/generate_data.py                     data generator (deterministic, seed 42)
public/css/app.css                           styles (no build step)
```

## Data model

| Table | Rows | Notes |
|---|---|---|
| `dw_brands` | 8 | one per `domain` |
| `dw_affiliates` | 1,200 | `affiliate_id` is a string like `AFF-10001` |
| `dw_players` | 300k | `player_idt` is the player's business key (not `id`); `affiliate_id` is NULL for organic players |
| `dw_deposit_transactions` | ~2.0M | amounts in player currency plus `amount_eur` |
| `dw_withdraw_transactions` | ~360k | |
| `dw_player_daily_activity` | ~1.7M | one row per player per active day, built by `bi:sync-daily-activity` |

All timestamps are UTC. The snapshot covers 2025-01-01 to 2026-10-31.

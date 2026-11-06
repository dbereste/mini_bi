# Mini BI

A small Laravel 10 dashboard on top of a SQLite "warehouse" of synthetic iGaming data
(brands, affiliates, players, deposits, withdrawals and daily player activity, about 4.4M rows in total).

Your assignment is in **[TASKS.md](TASKS.md)**.

## Running it

You need Docker with Compose v2.

**If you received the image file** (`mini-bi-1.0.tar.gz`):

```bash
docker load -i mini-bi-1.0.tar.gz
docker compose up -d
```

**If you only have the source**, build it yourself (generating the data takes 2-3 minutes):

```bash
docker compose up -d --build
```

Open http://localhost:8080. The first page load is slow, and that is part of the assignment.

If port 8080 is taken, choose another one: `APP_PORT=8081 docker compose up -d`.

Your working copy is mounted into the container, so edits on your machine are live.
`vendor/` and the database live in Docker volumes.

## Useful commands

```bash
docker compose exec app php artisan migrate          # run your migrations
docker compose exec app php artisan test             # run tests
docker compose exec app sqlite3 /data/database.sqlite   # SQL shell
docker compose exec app tail -f storage/logs/laravel.log

docker compose down -v && docker compose up -d       # reset the database to its original state
```

If you want `vendor/` on your machine too (for IDE autocompletion), run `composer install --no-scripts` locally.

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

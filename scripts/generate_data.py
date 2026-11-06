#!/usr/bin/env python3
"""
Generates the Mini BI SQLite warehouse with synthetic iGaming data.

Usage:
    python3 scripts/generate_data.py [output_path]

Environment:
    SCALE   multiplier for the number of players (default 1.0 = 300k players)
    SEED    random seed (default 42) - output is deterministic for a given seed
"""
import math
import os
import random
import sqlite3
import sys
import time
from datetime import datetime, timedelta, timezone

HERE = os.path.dirname(os.path.abspath(__file__))
SCHEMA = os.path.join(HERE, "..", "database", "schema", "schema.sql")

OUT = sys.argv[1] if len(sys.argv) > 1 else os.path.join(HERE, "..", "database", "database.sqlite")
SCALE = float(os.environ.get("SCALE", "1.0"))
SEED = int(os.environ.get("SEED", "42"))

START = datetime(2025, 1, 1, tzinfo=timezone.utc)
END = datetime(2026, 10, 31, 23, 59, 59, tzinfo=timezone.utc)
START_TS = START.timestamp()
END_TS = END.timestamp()
SPAN = END_TS - START_TS
DAY = 86400

N_PLAYERS = int(300_000 * SCALE)
N_AFFILIATES = 1_200

# Days on which the daily sync job was (accidentally) executed twice.
DOUBLE_SYNC_DAYS = {"2026-10-14", "2026-10-15"}

BRANDS = [
    # domain, code, name, currency, launched
    (1, "SPH", "SpinHarbor", "EUR", "2019-03-01 00:00:00"),
    (2, "LFJ", "LuckyFjord", "NOK", "2020-06-15 00:00:00"),
    (3, "RDG", "RedGoal", "BRL", "2021-02-01 00:00:00"),
    (4, "MPB", "MapleBet", "CAD", "2021-09-10 00:00:00"),
    (5, "VSP", "VistulaPlay", "PLN", "2022-04-04 00:00:00"),
    (6, "NBT", "NorthBet", "EUR", "2022-11-20 00:00:00"),
    (7, "SLC", "SlotCrown", "GBP", "2023-05-05 00:00:00"),
    (8, "STR", "StarReel", "USD", "2024-08-01 00:00:00"),
]
BRAND_WEIGHTS = [22, 9, 18, 11, 10, 14, 8, 8]
FX = {"EUR": 1.0, "NOK": 0.086, "BRL": 0.17, "CAD": 0.67, "PLN": 0.23, "GBP": 1.17, "USD": 0.92}
COUNTRIES = {
    1: ["Germany", "Austria", "Finland", "Ireland"],
    2: ["Norway"],
    3: ["Brazil"],
    4: ["Canada"],
    5: ["Poland"],
    6: ["Finland", "Estonia", "Germany", "Netherlands"],
    7: ["United Kingdom", "Ireland"],
    8: ["United States", "Mexico", "Chile"],
}
PAY_METHODS = [
    ("Visa", "Adyen"), ("Mastercard", "Adyen"), ("Skrill", "Paysafe"), ("Neteller", "Paysafe"),
    ("Trustly", "Trustly"), ("PIX", "PagBrasil"), ("Interac", "Gigadat"), ("BLIK", "Przelewy24"),
    ("Paysafecard", "Paysafe"), ("Bank Transfer", "LegacyPay"), ("Crypto", "CoinsPaid"),
]
PAY_WEIGHTS = [26, 20, 8, 6, 10, 9, 6, 5, 3, 5, 2]
PLATFORMS = ["web", "mobile", "app"]
PLATFORM_WEIGHTS = [38, 47, 15]

AFF_PREFIX = ["Casino", "Slot", "Bet", "Bonus", "Spin", "Jackpot", "Lucky", "Royal", "Golden", "Wild",
              "Vegas", "Ace", "Mega", "Top", "Prime", "Nordic", "Samba", "Maple", "Odds", "Reel"]
AFF_SUFFIX = ["Guru", "Media", "Partners", "Hub", "Review", "Insider", "Zone", "Planet", "Advisor",
              "Network", "Digital", "Club", "Finder", "Lab", "Arena", "Portal"]
AFF_FORM = ["Ltd", "Media", "Group", "OÜ", "GmbH", "LLC", "", "", ""]
MIGRATIONS = [
    "2026_01_01_000001_create_dw_brands_table",
    "2026_01_01_000002_create_dw_affiliates_table",
    "2026_01_01_000003_create_dw_players_table",
    "2026_01_01_000004_create_dw_deposit_transactions_table",
    "2026_01_01_000005_create_dw_withdraw_transactions_table",
    "2026_01_01_000006_create_dw_player_daily_activity_table",
]
MANAGERS = ["Ana Rusu", "Mihai Popa", "Elena Ciobanu", "Victor Lungu", "Irina Moraru", "Dan Cojocaru"]


def ts_to_str(ts: float) -> str:
    return datetime.fromtimestamp(ts, tz=timezone.utc).strftime("%Y-%m-%d %H:%M:%S")


def main() -> None:
    random.seed(SEED)
    t0 = time.time()

    if os.path.exists(OUT):
        os.remove(OUT)
    os.makedirs(os.path.dirname(os.path.abspath(OUT)), exist_ok=True)

    db = sqlite3.connect(OUT)
    db.execute("PRAGMA journal_mode = OFF")
    db.execute("PRAGMA synchronous = OFF")
    with open(SCHEMA) as fh:
        db.executescript(fh.read())

    # ---- Laravel migrations bookkeeping ------------------------------------------
    # The warehouse tables already exist, so mark their migrations as run.
    db.execute("CREATE TABLE migrations (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, "
               "migration VARCHAR NOT NULL, batch INTEGER NOT NULL)")
    db.executemany("INSERT INTO migrations (migration, batch) VALUES (?, 1)", [(m,) for m in MIGRATIONS])

    # ---- brands --------------------------------------------------------------
    db.executemany(
        "INSERT INTO dw_brands (domain, brand_code, name, default_currency, launched_at) VALUES (?,?,?,?,?)",
        BRANDS,
    )

    # ---- affiliates ----------------------------------------------------------
    affiliates = []
    used_names = set()
    for i in range(N_AFFILIATES):
        while True:
            name = f"{random.choice(AFF_PREFIX)}{random.choice(AFF_SUFFIX)} {random.choice(AFF_FORM)}".strip()
            if name not in used_names:
                break
            name = f"{name} {random.randint(2, 99)}"
            if name not in used_names:
                break
        used_names.add(name)
        affiliates.append([
            f"AFF-{10001 + i}",
            random.choices([b[0] for b in BRANDS], BRAND_WEIGHTS)[0],
            name,
            random.choices(["CPA", "RevShare", "Hybrid"], [35, 45, 20])[0],
            random.choice(MANAGERS),
            random.choices(["active", "paused", "terminated"], [80, 12, 8])[0],
            ts_to_str(START_TS - random.uniform(0, 900) * DAY),
        ])
    # A few "special" names that come from a partner self-service portal import.
    affiliates[36][2] = "Bet & Win Partners, Ltd"
    affiliates[211][2] = "Ölands Spelguide AB"
    affiliates[417][2] = "TopSlots <img src=x onerror=\"alert('Hello from affiliate AFF-10418')\"> Media"
    db.executemany(
        "INSERT INTO dw_affiliates (affiliate_id, domain, name, affiliate_type, account_manager, status, created) "
        "VALUES (?,?,?,?,?,?,?)",
        affiliates,
    )
    # Zipf-like traffic distribution; the last 60 affiliates never sent anybody.
    aff_ids = [a[0] for a in affiliates[:-60]]
    aff_weights = [1.0 / ((r + 1) ** 0.85) for r in range(len(aff_ids))]
    random.shuffle(aff_weights)
    aff_cum = []
    acc = 0.0
    for w in aff_weights:
        acc += w
        aff_cum.append(acc)

    brand_domains = [b[0] for b in BRANDS]
    brand_cur = {b[0]: b[3] for b in BRANDS}

    players_buf, dep_buf, wd_buf, daily_buf = [], [], [], []
    counts = {"players": 0, "deposits": 0, "withdrawals": 0, "daily": 0}
    tx_id = 90_000_000
    wd_id = 70_000_000

    def flush(force: bool = False) -> None:
        if players_buf and (force or len(players_buf) >= 20_000):
            db.executemany(
                "INSERT INTO dw_players (player_idt, domain, affiliate_id, btag, country_name, currency, platform, "
                "status, is_test, kyc_verified, created, first_deposit_date, first_deposit_amount_eur, rec_ins_date) "
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", players_buf)
            counts["players"] += len(players_buf)
            players_buf.clear()
        if dep_buf and (force or len(dep_buf) >= 100_000):
            db.executemany(
                "INSERT INTO dw_deposit_transactions (transaction_idt, player_idt, domain, pay_method_name, "
                "pay_system_name, platform, status, amount, amount_eur, currency, created, rec_ins_date) "
                "VALUES (?,?,?,?,?,?,?,?,?,?,?,?)", dep_buf)
            counts["deposits"] += len(dep_buf)
            dep_buf.clear()
        if wd_buf and (force or len(wd_buf) >= 100_000):
            db.executemany(
                "INSERT INTO dw_withdraw_transactions (transaction_idt, player_idt, domain, pay_method_name, "
                "status, amount, amount_eur, currency, created, rec_ins_date) VALUES (?,?,?,?,?,?,?,?,?,?)", wd_buf)
            counts["withdrawals"] += len(wd_buf)
            wd_buf.clear()
        if daily_buf and (force or len(daily_buf) >= 100_000):
            db.executemany(
                "INSERT INTO dw_player_daily_activity (player_idt, domain, reporting_date, deposit_amount_eur, "
                "deposit_count, withdraw_amount_eur, withdraw_count, ggr_casino_eur, ggr_sport_eur, bonus_eur, ngr, "
                "rec_ins_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)", daily_buf)
            counts["daily"] += len(daily_buf)
            daily_buf.clear()

    import bisect

    for p in range(N_PLAYERS):
        player_idt = 4_100_000 + p * 3 + random.randint(0, 2)
        domain = random.choices(brand_domains, BRAND_WEIGHTS)[0]
        currency = brand_cur[domain]
        fx = FX[currency]
        is_test = 1 if random.random() < 0.01 else 0
        if random.random() < 0.12:
            affiliate_id, btag = None, None
        else:
            affiliate_id = aff_ids[bisect.bisect_left(aff_cum, random.random() * acc)]
            btag = f"{affiliate_id[4:]}_{random.randint(100000, 999999)}"
        created_ts = START_TS + SPAN * math.sqrt(random.random())
        platform = random.choices(PLATFORMS, PLATFORM_WEIGHTS)[0]

        first_dep_ts = None
        first_dep_eur = None
        is_depositor = random.random() < (0.9 if is_test else 0.45)
        if is_depositor:
            first_dep_ts = created_ts + random.expovariate(1 / 2.0) * DAY
            if first_dep_ts > END_TS:
                is_depositor = False
                first_dep_ts = None

        status = "active"
        if not is_test and random.random() < 0.06:
            status = random.choice(["closed", "frozen", "self_excluded"])

        if is_depositor:
            seg = random.random()
            if is_test:
                mean_n, mu = 6, math.log(1500)
            elif seg < 0.70:
                mean_n, mu = 5, math.log(25)
            elif seg < 0.95:
                mean_n, mu = 25, math.log(60)
            else:
                mean_n, mu = 100, math.log(400)
            n_dep = 1 + int(random.expovariate(1 / mean_n))
            life_end = min(END_TS, first_dep_ts + random.expovariate(1 / 140.0) * DAY + DAY)

            day_dep = {}
            day_wd = {}
            for k in range(n_dep):
                if k == 0:
                    t = first_dep_ts
                else:
                    t = random.uniform(first_dep_ts, life_end)
                amount = round(max(5.0, random.lognormvariate(mu, 0.8)) / fx, 2)
                pay_method, pay_system = random.choices(PAY_METHODS, PAY_WEIGHTS)[0]
                if k == 0:
                    st = "approved"
                else:
                    st = random.choices(["approved", "declined", "pending"], [86, 9, 5])[0]
                if st == "approved" and pay_system == "LegacyPay":
                    st = "Approved"
                amount_eur = round(amount * fx, 2)
                if k == 0:
                    first_dep_eur = amount_eur
                tx_id += random.randint(1, 5)
                created_s = ts_to_str(t)
                dep_buf.append((tx_id, player_idt, domain, pay_method, pay_system,
                                random.choices(PLATFORMS, PLATFORM_WEIGHTS)[0], st, amount, amount_eur,
                                currency, created_s, ts_to_str(t + random.uniform(60, 7200))))
                if st.lower() == "approved":
                    d = created_s[:10]
                    s, c = day_dep.get(d, (0.0, 0))
                    day_dep[d] = (s + amount_eur, c + 1)

            n_wd = sum(1 for _ in range(n_dep) if random.random() < 0.18)
            for _ in range(n_wd):
                t = random.uniform(first_dep_ts + DAY, life_end + DAY)
                if t > END_TS:
                    continue
                amount = round(max(10.0, random.lognormvariate(mu + 0.6, 0.9)) / fx, 2)
                st = random.choices(["approved", "rejected", "pending"], [80, 12, 8])[0]
                amount_eur = round(amount * fx, 2)
                wd_id += random.randint(1, 5)
                created_s = ts_to_str(t)
                wd_buf.append((wd_id, player_idt, domain, random.choices(PAY_METHODS, PAY_WEIGHTS)[0][0], st,
                               amount, amount_eur, currency, created_s, ts_to_str(t + random.uniform(600, 86400))))
                if st == "approved":
                    d = created_s[:10]
                    s, c = day_wd.get(d, (0.0, 0))
                    day_wd[d] = (s + amount_eur, c + 1)

            active_days = set(day_dep) | set(day_wd)
            for _ in range(int(len(day_dep) * 0.6)):
                active_days.add(ts_to_str(random.uniform(first_dep_ts, life_end))[:10])

            plays_sport = random.random() < 0.3
            for d in sorted(active_days):
                dep_s, dep_c = day_dep.get(d, (0.0, 0))
                wd_s, wd_c = day_wd.get(d, (0.0, 0))
                base = dep_s if dep_s > 0 else math.exp(mu) * 0.4
                ggr_c = round(random.gauss(base * 0.3, base * 0.5), 2)
                ggr_s = round(random.gauss(base * 0.1, base * 0.3), 2) if plays_sport else 0.0
                bonus = round(base * random.uniform(0.02, 0.15), 2) if random.random() < 0.35 else 0.0
                ngr = round(ggr_c + ggr_s - bonus, 2)
                ins = f"{d} 02:{random.randint(0, 59):02d}:{random.randint(0, 59):02d}"
                row = (player_idt, domain, d, round(dep_s, 2), dep_c, round(wd_s, 2), wd_c, ggr_c, ggr_s, bonus,
                       ngr, ins)
                daily_buf.append(row)
                if d in DOUBLE_SYNC_DAYS:
                    daily_buf.append(row[:-1] + (f"{d} 09:41:{random.randint(10, 59):02d}",))

        kyc = random.choices(["yes", "no", "pending"], [55, 30, 15])[0] if is_depositor else "no"
        players_buf.append((
            player_idt, domain, affiliate_id, btag, random.choice(COUNTRIES[domain]), currency, platform, status,
            is_test, kyc, ts_to_str(created_ts),
            ts_to_str(first_dep_ts) if first_dep_ts else None, first_dep_eur,
            ts_to_str(created_ts + random.uniform(300, 3600)),
        ))
        flush()
        if (p + 1) % 50_000 == 0:
            print(f"  {p + 1:,} players ... {time.time() - t0:.0f}s", flush=True)

    flush(force=True)
    db.commit()
    db.execute("VACUUM")
    db.close()

    size_mb = os.path.getsize(OUT) / 1024 / 1024
    print(f"Done in {time.time() - t0:.0f}s -> {OUT} ({size_mb:.0f} MB)")
    for k, v in counts.items():
        print(f"  {k:12s} {v:>12,}")


if __name__ == "__main__":
    main()

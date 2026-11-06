-- Mini BI warehouse schema (SQLite).
-- Mirrors the Laravel migrations in database/migrations.

CREATE TABLE IF NOT EXISTS dw_brands (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    domain INTEGER NOT NULL,
    brand_code VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    default_currency VARCHAR(3) NOT NULL,
    launched_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS dw_affiliates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    affiliate_id VARCHAR(20) NOT NULL,
    domain INTEGER NULL,
    name VARCHAR(255) NOT NULL,
    affiliate_type VARCHAR(30) NULL,
    account_manager VARCHAR(100) NULL,
    status VARCHAR(20) NULL,
    created DATETIME NULL
);

CREATE TABLE IF NOT EXISTS dw_players (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_idt INTEGER NOT NULL,
    domain INTEGER NOT NULL,
    affiliate_id VARCHAR(20) NULL,
    btag VARCHAR(100) NULL,
    country_name VARCHAR(50) NULL,
    currency VARCHAR(3) NULL,
    platform VARCHAR(20) NULL,
    status VARCHAR(20) NULL,
    is_test INTEGER NOT NULL DEFAULT 0,
    kyc_verified VARCHAR(10) NULL,
    created DATETIME NULL,
    first_deposit_date DATETIME NULL,
    first_deposit_amount_eur NUMERIC NULL,
    rec_ins_date DATETIME NULL
);

CREATE TABLE IF NOT EXISTS dw_deposit_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_idt INTEGER NOT NULL,
    player_idt INTEGER NOT NULL,
    domain INTEGER NOT NULL,
    pay_method_name VARCHAR(50) NULL,
    pay_system_name VARCHAR(30) NULL,
    platform VARCHAR(20) NULL,
    status VARCHAR(20) NULL,
    amount NUMERIC NULL,
    amount_eur NUMERIC NULL,
    currency VARCHAR(3) NULL,
    created DATETIME NULL,
    rec_ins_date DATETIME NULL
);

CREATE TABLE IF NOT EXISTS dw_withdraw_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_idt INTEGER NOT NULL,
    player_idt INTEGER NOT NULL,
    domain INTEGER NOT NULL,
    pay_method_name VARCHAR(50) NULL,
    status VARCHAR(20) NULL,
    amount NUMERIC NULL,
    amount_eur NUMERIC NULL,
    currency VARCHAR(3) NULL,
    created DATETIME NULL,
    rec_ins_date DATETIME NULL
);

CREATE TABLE IF NOT EXISTS dw_player_daily_activity (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    player_idt INTEGER NOT NULL,
    domain INTEGER NOT NULL,
    reporting_date DATE NOT NULL,
    deposit_amount_eur NUMERIC NULL,
    deposit_count INTEGER NULL,
    withdraw_amount_eur NUMERIC NULL,
    withdraw_count INTEGER NULL,
    ggr_casino_eur NUMERIC NULL,
    ggr_sport_eur NUMERIC NULL,
    bonus_eur NUMERIC NULL,
    ngr NUMERIC NULL,
    rec_ins_date DATETIME NULL
);

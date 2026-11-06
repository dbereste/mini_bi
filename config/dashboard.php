<?php

return [
    // The warehouse snapshot ends on 2026-10-31, so default to its last full month.
    'default_from' => env('DASHBOARD_DEFAULT_FROM', '2026-10-01'),
    'default_to' => env('DASHBOARD_DEFAULT_TO', '2026-10-31'),

    // Number of affiliates shown in the performance table.
    'table_limit' => 100,
];

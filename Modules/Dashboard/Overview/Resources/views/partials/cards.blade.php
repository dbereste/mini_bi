<div class="kpi-row">
    <div class="kpi-card">
        <div class="kpi-label">
            Total Deposits
            <i class="bi bi-info-circle info-tip" title="Sum of approved deposits in EUR for the selected period"></i>
        </div>
        <div class="kpi-value">€{{ number_format($cards['deposits'], 2) }}</div>
        <div class="kpi-sub">
            {{ number_format($cards['deposit_count']) }} deposits · avg €{{ number_format($cards['avg_deposit'], 2) }}
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-label">
            Active Players
            <i class="bi bi-info-circle info-tip" title="Unique players with activity in the selected period"></i>
        </div>
        <div class="kpi-value">{{ number_format($cards['active_players']) }}</div>
        <div class="kpi-sub">{{ $from }} → {{ $to }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-label">
            FTD Conversion
            <i class="bi bi-info-circle info-tip" title="First-time depositors divided by registrations in the selected period"></i>
        </div>
        <div class="kpi-value">{{ $cards['conversion'] }}%</div>
        <div class="kpi-sub">
            {{ number_format($cards['ftds']) }} FTDs / {{ number_format($cards['registrations']) }} registrations
        </div>
    </div>
</div>

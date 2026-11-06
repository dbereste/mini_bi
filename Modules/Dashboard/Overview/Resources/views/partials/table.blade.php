@php
    $sortLink = function ($column) use ($from, $to, $search, $sort, $dir) {
        $nextDir = ($sort === $column && $dir === 'desc') ? 'asc' : 'desc';
        return route('overview', ['from' => $from, 'to' => $to, 'q' => $search, 'sort' => $column, 'dir' => $nextDir]);
    };
@endphp

<div class="panel">
    <div class="panel-header">
        <h2>Top affiliates</h2>
        <span class="text-soft">{{ count($affiliates) }} affiliates · {{ $from }} → {{ $to }}</span>
    </div>

    <table class="table table-hover perf-table">
        <thead>
            <tr>
                <th><a href="{{ $sortLink('a.affiliate_id') }}">ID</a></th>
                <th><a href="{{ $sortLink('a.name') }}">Affiliate</a></th>
                <th>Brand</th>
                <th>Type</th>
                <th>Manager</th>
                <th class="num">Registrations</th>
                <th class="num">FTDs</th>
                <th class="num"><a href="{{ $sortLink('depositors') }}">Depositors</a></th>
                <th class="num"><a href="{{ $sortLink('deposits') }}">Deposits (EUR)</a></th>
                <th class="num"><a href="{{ $sortLink('ngr') }}">NGR (EUR)</a></th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($affiliates as $affiliate)
                <tr>
                    <td class="text-soft">{{ $affiliate->affiliate_id }}</td>
                    <td class="aff-name">{!! $affiliate->name !!}</td>
                    <td>{{ $affiliate->brand->name ?? '—' }}</td>
                    <td>{{ $affiliate->affiliate_type }}</td>
                    <td>{{ $affiliate->account_manager }}</td>
                    <td class="num">{{ number_format($affiliate->players()->count()) }}</td>
                    <td class="num">{{ number_format($affiliate->players()->whereBetween('first_deposit_date', [$from, $to])->count()) }}</td>
                    <td class="num">{{ number_format($affiliate->depositors) }}</td>
                    <td class="num">{{ number_format($affiliate->deposits, 2) }}</td>
                    <td class="num {{ $affiliate->ngr < 0 ? 'neg' : '' }}">{{ number_format($affiliate->ngr, 2) }}</td>
                    <td><span class="status status-{{ $affiliate->status }}">{{ $affiliate->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-soft">No data for the selected period.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

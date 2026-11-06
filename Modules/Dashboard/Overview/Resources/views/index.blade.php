@extends('layouts.app')

@section('title', 'Overview · Mini BI')
@section('page-title', 'Affiliate Performance Overview')

@section('content')
    <form method="GET" action="{{ route('overview') }}" class="filters">
        <div class="filter-item">
            <label>From</label>
            <input type="text" name="from" value="{{ $from }}" placeholder="YYYY-MM-DD">
        </div>
        <div class="filter-item">
            <label>To</label>
            <input type="text" name="to" value="{{ $to }}" placeholder="YYYY-MM-DD">
        </div>
        <div class="filter-item">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search affiliate...">
        </div>
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $dir }}">
        <button type="submit" class="btn btn-primary btn-apply">Apply</button>
        <a href="{{ route('overview.export', ['from' => $from, 'to' => $to, 'q' => $search]) }}" class="btn btn-outline-secondary btn-export">
            <i class="bi bi-download"></i> CSV
        </a>
    </form>

    @include('overview::partials.cards')

    @include('overview::partials.table')
@endsection

@extends('layouts.app')

@section('title', 'Activity Statistics | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('activity.index') }}" class="mb-2 inline-flex items-center text-sm font-semibold text-[#42d3c0] hover:text-[#5ee7df]"><i class="bi bi-chevron-left mr-1"></i>Activity Log</a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">Activity</p>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-100 sm:text-2xl">Activity Statistics</h1>
            <p class="mt-1 text-sm text-slate-500">Aggregated activity from the existing QAD transaction log.</p>
            <p class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#293031] bg-[#171b1c] px-3 py-2 text-xs font-semibold text-slate-400"><i class="bi bi-calendar-range text-[#0f9f91]"></i>Rentang data: {{ $dataDateRange['from']?->format('d M Y') ?: '-' }} sampai {{ $dataDateRange['to']?->format('d M Y') ?: '-' }}</p>
            @if ($dateRangeLabel)
                <p class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#294b49] bg-[#162827] px-3 py-2 text-xs font-semibold text-[#42d3c0]"><i class="bi bi-calendar-range"></i>{{ $dateRangeLabel }}</p>
            @endif
        </div>
        <form method="GET" action="{{ route('activity.statistics') }}" class="flex flex-wrap items-end gap-2" data-auto-filter>
            <label class="text-xs text-slate-500">From<input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="ml-2 rounded-lg border border-[#293031] bg-[#111516] px-3 py-2 text-xs text-slate-200"></label>
            <label class="text-xs text-slate-500">To<input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="ml-2 rounded-lg border border-[#293031] bg-[#111516] px-3 py-2 text-xs text-slate-200"></label>
            <a href="{{ route('activity.statistics') }}" class="rounded-lg bg-[#293031] px-3 py-2 text-xs font-semibold text-slate-300 hover:bg-[#3c494a]">Reset</a>
        </form>
    </div>

    <section class="grid gap-3 sm:grid-cols-3">
        @foreach ([['label' => 'Activities', 'value' => $stats['total_activities'] ?? 0, 'icon' => 'activity'], ['label' => 'Unique Users', 'value' => $stats['unique_users'] ?? 0, 'icon' => 'people'], ['label' => 'Unique Programs', 'value' => $stats['unique_programs'] ?? 0, 'icon' => 'diagram-3']] as $metric)
            <div class="rounded-lg border border-[#293031] bg-[#171b1c] p-4 shadow-lg shadow-black/10"><div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}</span><i class="bi bi-{{ $metric['icon'] }} text-lg text-[#42d3c0]"></i></div><p class="mt-3 text-2xl font-extrabold text-slate-100">{{ number_format($metric['value']) }}</p></div>
        @endforeach
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] p-4"><h2 class="mb-3 text-base font-bold text-slate-100">Activity by Date</h2><div class="h-[260px]"><canvas id="dateChart"></canvas></div></div>
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] p-4"><h2 class="mb-3 text-base font-bold text-slate-100">Activity by Hour</h2><div class="h-[260px]"><canvas id="hourChart"></canvas></div></div>
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] p-4"><h2 class="mb-3 text-base font-bold text-slate-100">Activity by Module</h2><div class="h-[260px]"><canvas id="moduleChart"></canvas></div></div>
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] p-4"><h2 class="mb-3 text-base font-bold text-slate-100">Activity by Type</h2><div class="h-[260px]"><canvas id="typeChart"></canvas></div></div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const stats = @json($stats);
    const hourly = @json($hourlyData);
    const teal = '#2dd4bf';
    const lightTheme = document.body.classList.contains('light-theme');
    const chartText = lightTheme ? '#475569' : '#64748b';
    const chartGrid = lightTheme ? '#d7e0e1' : '#293031';
    const options = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { color: chartText, font: { size: 10 } } }, y: { beginAtZero: true, grid: { color: chartGrid }, ticks: { color: chartText } } } };
    const bar = (id, labels, values, color = teal) => { const el = document.getElementById(id); if (!el || typeof Chart === 'undefined') return; new Chart(el, { type: 'bar', data: { labels, datasets: [{ data: values, backgroundColor: color, borderRadius: 4, barThickness: 14 }] }, options }); };
    bar('dateChart', Object.keys(stats.activity_by_date || {}), Object.values(stats.activity_by_date || {}));
    bar('hourChart', Object.keys(hourly).map(hour => `${String(hour).padStart(2, '0')}:00`), Object.values(hourly), '#60a5fa');
    bar('moduleChart', Object.keys(stats.activity_by_module || {}), Object.values(stats.activity_by_module || {}), '#fbbf24');
    bar('typeChart', Object.keys(stats.activity_by_type || {}), Object.values(stats.activity_by_type || {}), '#a78bfa');
    document.querySelectorAll('[data-auto-filter]').forEach(form => form.querySelectorAll('input, select').forEach(field => field.addEventListener('change', () => form.requestSubmit())));
})();
</script>
@endpush

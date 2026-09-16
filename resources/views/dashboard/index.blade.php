@extends('layouts.app')

@section('title', 'Dashboard | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">QAD Monitoring</p>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-100 sm:text-2xl">Operations overview</h1>
            <p class="mt-1 text-sm text-slate-500">Recorded system activity and usage performance.</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('dashboard') }}" class="hidden items-center gap-2 lg:flex">
                <label class="sr-only" for="dashboardDateFrom">Date from</label>
                <input id="dashboardDateFrom" type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border border-[#293031] bg-[#171b1c] px-2 py-2 text-xs text-slate-300 focus:border-[#0f9f91] focus:outline-none">
                <span class="text-xs text-slate-600">to</span>
                <label class="sr-only" for="dashboardDateTo">Date to</label>
                <input id="dashboardDateTo" type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border border-[#293031] bg-[#171b1c] px-2 py-2 text-xs text-slate-300 focus:border-[#0f9f91] focus:outline-none">
                <button type="submit" class="rounded-lg border border-[#0f9f91] px-3 py-2 text-xs font-semibold text-[#42d3c0] transition hover:bg-[#0f9f91] hover:text-white">Apply</button>
            </form>
            <span class="rounded-lg border border-[#293031] bg-[#171b1c] px-4 py-2 text-sm text-slate-400">
                <i class="bi bi-calendar-range mr-2 text-[#0f9f91]"></i>Rentang data: {{ $dataDateRange['from']?->format('d M Y') ?: '-' }} sampai {{ $dataDateRange['to']?->format('d M Y') ?: '-' }}
            </span>
        </div>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            ['label' => 'Total Users', 'value' => $kpis['total_users'], 'icon' => 'people-fill', 'accent' => 'text-[#42d3c0]'],
            ['label' => 'Active Users', 'value' => $kpis['active_users'], 'icon' => 'person-check-fill', 'accent' => 'text-[#67e8f9]'],
            ['label' => 'Total Modules', 'value' => $kpis['total_modules'], 'icon' => 'boxes', 'accent' => 'text-[#60a5fa]'],
            ['label' => 'Entryan', 'value' => $kpis['entryan'], 'icon' => 'box-arrow-in-right', 'accent' => 'text-[#fbbf24]'],
            ['label' => 'Laporan', 'value' => $kpis['laporan'], 'icon' => 'file-earmark-bar-graph-fill', 'accent' => 'text-[#a78bfa]'],
            ['label' => 'Activities', 'value' => $kpis['activities'], 'icon' => 'arrow-repeat', 'accent' => 'text-[#fb7185]'],
        ] as $metric)
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-4 shadow-lg shadow-black/10 transition hover:border-[#3c494a]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $metric['label'] }}</span>
                        <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-100">{{ number_format($metric['value']) }}</p>
                    </div>
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-[#334344] bg-[#203233] {{ $metric['accent'] }} shadow-inner shadow-black/20">
                        <i class="bi bi-{{ $metric['icon'] }} text-xl"></i>
                    </span>
                </div>
                <div class="mt-4 h-1 overflow-hidden rounded-full bg-[#202829]"><div class="h-full w-1/2 rounded-full bg-current {{ $metric['accent'] }} opacity-70"></div></div>
            </div>
        @endforeach
    </section>

    <section class="min-w-0 overflow-hidden rounded-lg border border-[#293031] bg-[#171b1c] p-4 shadow-lg shadow-black/10">
        <div class="mb-3 flex items-center justify-between">
            <div><h2 class="text-lg font-bold text-slate-100">Module Usage</h2><p class="mt-1 text-sm text-slate-500">Activity trend by module</p></div>
            <i class="bi bi-graph-up text-[#fbbf24]"></i>
        </div>
        <div class="dashboard-chart h-[360px] min-w-0"><canvas id="moduleUsageChart"></canvas></div>
    </section>

    <section class="grid min-w-0 gap-4 xl:grid-cols-2">
        <div class="min-w-0 overflow-hidden rounded-lg border border-[#293031] bg-[#171b1c] p-4 shadow-lg shadow-black/10">
            <div class="mb-3 flex items-center justify-between">
                <div><h2 class="text-lg font-bold text-slate-100">Activity Trend</h2><p class="mt-1 text-sm text-slate-500">{{ $dateRangeLabel ?: "Last {$days} days from latest recorded activity" }}</p></div>
                <i class="bi bi-graph-up-arrow text-[#0f9f91]"></i>
            </div>
            <div class="dashboard-chart h-[300px] min-w-0"><canvas id="activityTrendChart"></canvas></div>
        </div>
        <div class="min-w-0 overflow-hidden rounded-lg border border-[#293031] bg-[#171b1c] p-4 shadow-lg shadow-black/10">
            <div class="mb-3 flex items-center justify-between">
                <div><h2 class="text-lg font-bold text-slate-100">Top Programs</h2><p class="mt-1 text-sm text-slate-500">Top 10 by activity in the monitoring window</p></div>
                <a href="{{ route('activity.index') }}" class="text-xs font-semibold text-[#42d3c0] hover:text-[#5ee7df]">Activity log</a>
            </div>
            <div class="dashboard-chart h-[300px] min-w-0"><canvas id="topProgramsChart"></canvas></div>
        </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-[#293031] bg-[#171b1c] shadow-lg shadow-black/10">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#293031] px-4 py-3">
            <div><h2 class="text-lg font-bold text-slate-100">Recent Activity</h2><p class="mt-1 text-sm text-slate-500">Latest transactions recorded by QAD</p></div>
            <a href="{{ route('activity.index') }}" class="rounded-lg border border-[#3c494a] px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-[#0f9f91] hover:text-[#42d3c0]">Open activity log <i class="bi bi-arrow-up-right ml-1"></i></a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-[#293031] bg-[#151a1b]"><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">User</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Program</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Module</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th></tr></thead>
                <tbody class="divide-y divide-[#293031]">
                    @forelse ($recentActivities as $activity)
                        <tr class="transition hover:bg-[#1e2324]">
                            <td class="whitespace-nowrap px-5 py-3 text-slate-300">{{ optional($activity->activity_date)->format('d M Y') ?: '-' }}<span class="ml-2 text-xs text-slate-600">{{ $activity->activity_time ?? '-' }}</span></td>
                            <td class="px-5 py-3">@if ($activity->user)<a href="{{ route('users.show', $activity->user->id) }}" class="font-semibold text-[#42d3c0] hover:text-[#5ee7df]">{{ $activity->user->user_id }}</a><div class="text-xs text-slate-600">{{ $activity->user->user_name }}</div>@else<span class="text-slate-500">Unmapped user</span>@endif</td>
                            <td class="px-5 py-3 text-slate-200">{{ optional($activity->program)->name ?: 'Unmapped program' }}</td>
                            <td class="px-5 py-3 text-slate-400">{{ optional(optional($activity->program)->module)->name ?: 'Unmapped module' }}</td>
                            <td class="px-5 py-3"><span class="rounded px-2 py-1 text-xs font-semibold {{ $activity->status === 'GD' ? 'bg-[#1e3c3a] text-[#2dd4bf]' : 'bg-[#293031] text-slate-300' }}">{{ $activity->status ?: '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">No activity data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const colors = ['#0f9f91', '#42d3c0', '#2dd4bf', '#67e8f9', '#fbbf24', '#f59e0b', '#fb7185', '#a78bfa', '#60a5fa', '#94a3b8'];
    const lightTheme = document.body.classList.contains('light-theme');
    const chartText = lightTheme ? '#475569' : '#aab7b8';
    const chartMuted = lightTheme ? '#64748b' : '#718082';
    const chartGrid = lightTheme ? '#d7e0e1' : '#293031';
    const chartSurface = lightTheme ? '#ffffff' : '#111516';
const baseChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: chartSurface,
            titleColor: chartText,
            bodyColor: chartText,
            borderColor: chartGrid,
            borderWidth: 1
        }
    }
};

const horizontalBarOptions = {
    ...baseChartOptions,
    indexAxis: 'y',
    scales: {
        x: {
            beginAtZero: true,
            grid: { color: chartGrid },
            ticks: {
                color: chartMuted,
                font: { size: 10 }
            }
        },
        y: {
            grid: { display: false },
            ticks: {
                color: chartText,
                autoSkip: false,
                font: { size: 10 },
                callback: function(value) {
                    const label = this.getLabelForValue(value);
                    return label.length > 24
                        ? `${label.slice(0, 23)}...`
                        : label;
                }
            }
        }
    }
};

const makeBar = (id, labels, values, color, options, datasetOptions = {}) => {
    const canvas = document.getElementById(id);
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: color,
                borderRadius: 4,
                ...datasetOptions
            }]
        },
        options: options
    });
};
    const trend = @json($activityTrend);
    if (typeof Chart !== 'undefined' && document.getElementById('activityTrendChart')) {
        new Chart(document.getElementById('activityTrendChart'), { type: 'line', data: { labels: trend.map(item => item.date), datasets: [{ data: trend.map(item => item.count), borderColor: '#0f9f91', backgroundColor: 'rgba(15, 159, 145, .12)', borderWidth: 2, fill: true, tension: .35, pointRadius: 2 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: chartSurface, titleColor: chartText, bodyColor: chartText, borderColor: chartGrid, borderWidth: 1 } }, scales: { x: { grid: { display: false }, ticks: { color: chartMuted } }, y: { beginAtZero: true, grid: { color: chartGrid }, ticks: { color: chartMuted } } } } });
    }
const modules = @json($moduleUsage);

if (typeof Chart !== 'undefined' && document.getElementById('moduleUsageChart')) {
    new Chart(document.getElementById('moduleUsageChart'), {
        type: 'line',
        data: {
            labels: modules.map(item => item.module_name),
            datasets: [{
                data: modules.map(item => item.activity_count),
                borderColor: '#fbbf24',
                backgroundColor: 'rgba(251, 191, 36, .08)',
                borderWidth: 3,
                borderDash: [6, 4],
                pointBackgroundColor: '#fbbf24',
                pointBorderColor: chartSurface,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0,
                stepped: true
            }]
        },
        options: {
            ...baseChartOptions,
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: chartText, autoSkip: false, maxRotation: 45, minRotation: 0 }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: chartGrid },
                    ticks: { color: chartMuted }
                }
            }
        }
    });
}

const programs = @json($programUsage);

makeBar(
    'topProgramsChart',
    programs.map(item => item.program_name || item.program_code),
    programs.map(item => item.activity_count),
    '#60a5fa',
    horizontalBarOptions,
    { barThickness: 12 }
);
})();
</script>
@endpush

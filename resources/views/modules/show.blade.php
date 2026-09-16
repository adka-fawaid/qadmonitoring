@extends('layouts.app')

@section('title', 'Module: ' . $module['name'] . ' | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ $backUrl }}" class="inline-flex items-center text-[#42d3c0] hover:text-[#52d5c5] mb-2 text-sm font-semibold">
                <i class="bi bi-chevron-left mr-1"></i>{{ $backLabel }}
            </a>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-100">{{ $module['name'] }}</h1>
            <p class="mt-1 text-sm text-slate-500">Module overview and usage analytics</p>
        </div>
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] px-4 py-2 text-sm text-slate-400">
            <i class="bi bi-hourglass-split mr-2 text-[#0f9f91]"></i>
            <span>{{ $days }} days</span>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Programs', 'value' => $module['total_programs'], 'icon' => 'diagram-3'],
            ['label' => 'Activities', 'value' => $module['total_activity_count'], 'icon' => 'activity'],
            ['label' => 'Unique Users', 'value' => $module['unique_users'], 'icon' => 'people'],
            ['label' => 'Programs Used', 'value' => $module['unique_programs_used'], 'icon' => 'play-circle'],
        ] as $kpi)
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500">{{ $kpi['label'] }}</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#203233] text-[#2dd4bf]">
                        <i class="bi bi-{{ $kpi['icon'] }}"></i>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-extrabold text-slate-100">{{ number_format($kpi['value']) }}</p>
            </div>
        @endforeach
    </div>

    <!-- Activity Trend & Usage Breakdown -->
    <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
        <!-- Activity Trend -->
        <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-100">Activity Trend</h2>
                <p class="mt-1 text-sm text-slate-500">Activity over the last {{ $days }} days</p>
            </div>
            <div class="h-[300px]">
                <canvas id="activityTrendChart"></canvas>
            </div>
        </section>

        <!-- Top Users & Programs -->
        <div class="space-y-6">
            <!-- Top Users -->
            <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h3 class="text-lg font-bold text-slate-100 mb-4">Top Users</h3>
                <div class="space-y-2">
                    @forelse ($usage['top_users'] as $user)
                        <div class="flex items-center justify-between pb-2 border-b border-[#252c2d] last:border-0">
                            <div>
                                <p class="font-semibold text-slate-200">{{ $user['user_name'] }}</p>
                                <p class="text-xs text-slate-600">{{ $user['user_id'] }}</p>
                            </div>
                            <span class="bg-[#243233] px-2 py-1 rounded text-xs font-semibold text-slate-300">
                                {{ number_format($user['activity_count']) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-slate-600 text-sm">No user activity data</p>
                    @endforelse
                </div>
            </section>

            <!-- Top Programs in Module -->
            <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h3 class="text-lg font-bold text-slate-100 mb-4">Top Programs</h3>
                <div class="space-y-2">
                    @forelse ($usage['top_programs'] as $program)
                        <div class="flex items-center justify-between pb-2 border-b border-[#252c2d] last:border-0">
                            <div>
                                <p class="font-semibold text-slate-200">{{ $program['program_name'] }}</p>
                                <p class="text-xs text-slate-600">{{ $program['program_code'] }}</p>
                            </div>
                            <span class="bg-[#243233] px-2 py-1 rounded text-xs font-semibold text-slate-300">
                                {{ number_format($program['activity_count']) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-slate-600 text-sm">No program activity data</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <!-- Programs in Module -->
    <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-100">Programs in {{ $module['name'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">All registered programs with usage statistics</p>
            </div>
            <span class="rounded-full bg-[#123c38] px-3 py-1 text-xs font-semibold text-[#42d3c0]">
                {{ $programs->total() }} programs
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#293031]">
                        <th class="px-4 py-3 text-left font-semibold text-slate-300">Program Code</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-300">Program Name</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-300">Activities</th>
                        <th class="px-4 py-3 text-center font-semibold text-slate-300">Unique Users</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#293031]">
                    @forelse ($programs as $program)
                        <tr class="hover:bg-[#1e2324] transition">
                            <td class="px-4 py-3 font-semibold text-slate-200">{{ $program->program_code ?: '-' }}</td>
                            <td class="px-4 py-3 text-slate-200">{{ $program->name ?: '-' }}</td>
                            <td class="px-4 py-3 text-center text-slate-300">{{ number_format($program->activity_count ?? 0) }}</td>
                            <td class="px-4 py-3 text-center text-slate-300">{{ $program->unique_users ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                No programs found in this module.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($programs->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $programs->links('pagination::tailwind') }}
            </div>
        @endif
    </section>

    <!-- Implementation Status -->
    @if (!empty($implementationStatus['implementations']))
        <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
            <h2 class="text-lg font-bold text-slate-100 mb-6">Implementation Status</h2>

            <div class="grid gap-4">
                @foreach ($implementationStatus['implementations'] as $impl)
                    <div class="rounded-lg border border-[#293031] bg-[#111516] p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-slate-200">{{ $impl['sub_module'] }}</h3>
                            </div>
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold 
                                @if ($impl['implementation_status'] === 'Complete') bg-[#1e3c3a] text-[#2dd4bf]
                                @elseif ($impl['implementation_status'] === 'In Progress') bg-[#3c2f1e] text-[#fbbf24]
                                @else bg-[#2a3a3f] text-slate-300 @endif">
                                {{ $impl['implementation_status'] ?: 'Pending' }}
                            </span>
                        </div>

                        <div class="grid gap-3">
                            <div class="grid grid-cols-2 text-sm">
                                <div>
                                    <p class="text-xs text-slate-600 mb-1">Possible</p>
                                    <p class="font-semibold text-slate-300">{{ $impl['implementation_possible'] ?: '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-600 mb-1">Investment</p>
                                    <p class="font-semibold text-slate-300">{{ $impl['investment'] ?: '-' }}</p>
                                </div>
                            </div>
                            @if ($impl['notes'])
                                <div>
                                    <p class="text-xs text-slate-600 mb-1">Notes</p>
                                    <p class="text-sm text-slate-400">{{ $impl['notes'] }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
    if (typeof Chart !== 'undefined') {
        const lightTheme = document.body.classList.contains('light-theme');
        const chartText = lightTheme ? '#475569' : '#aab7b8';
        const chartMuted = lightTheme ? '#64748b' : '#718082';
        const chartGrid = lightTheme ? '#d7e0e1' : '#293031';
        const chartSurface = lightTheme ? '#ffffff' : '#111516';
        const canvas = document.getElementById('activityTrendChart');
        if (canvas) {
            const trendData = @json($activityTrend);
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.date),
                    datasets: [{
                        label: 'Activities',
                        data: trendData.map(d => d.count),
                        backgroundColor: 'rgba(15, 159, 145, 0.1)',
                        borderColor: '#0f9f91',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointBackgroundColor: '#0f9f91',
                        pointBorderColor: '#171b1c',
                        pointBorderWidth: 2
                    }]
                },
                options: {
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
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: chartText } },
                        y: { beginAtZero: true, grid: { color: chartGrid }, ticks: { color: chartMuted } }
                    }
                }
            });
        }
    }
</script>
@endpush

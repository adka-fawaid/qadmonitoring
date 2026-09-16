@extends('layouts.app')

@section('title', 'Activity Log | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">Activity</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-100 sm:text-3xl">Activity Log</h1>
            <p class="mt-1 text-sm text-slate-500">All user transactions and program activities.</p>
            <p class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#293031] bg-[#171b1c] px-3 py-2 text-xs font-semibold text-slate-400"><i class="bi bi-calendar-range text-[#0f9f91]"></i>Rentang data: {{ $dataDateRange['from']?->format('d M Y') ?: '-' }} sampai {{ $dataDateRange['to']?->format('d M Y') ?: '-' }}</p>
            @if ($dateRangeLabel)
                <p class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#294b49] bg-[#162827] px-3 py-2 text-xs font-semibold text-[#42d3c0]"><i class="bi bi-calendar-range"></i>{{ $dateRangeLabel }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <div class="rounded-lg border border-[#293031] bg-[#171b1c] px-4 py-2 text-sm text-slate-400">
                <i class="bi bi-activity mr-2 text-[#0f9f91]"></i>
                <span>{{ number_format($activities->total()) }} activities</span>
            </div>
            <a href="{{ route('activity.export', request()->query()) }}" class="rounded-lg border border-[#0f9f91] bg-[#0f9f91] px-4 py-2 text-sm font-semibold text-white hover:bg-[#42d3c0] transition">
                <i class="bi bi-download mr-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-5">
        <form method="GET" action="{{ route('activity.index') }}" class="grid gap-4" data-auto-filter>
            <div class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm focus:outline-none focus:border-[#0f9f91]">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" 
                           class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm focus:outline-none focus:border-[#0f9f91]">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">User</label>
                    <input type="text" name="user_id" value="{{ request('user_id') }}" placeholder="User ID" 
                           class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm placeholder-slate-600 focus:outline-none focus:border-[#0f9f91]">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm focus:outline-none focus:border-[#0f9f91]">
                        <option value="">All Status</option>
                        <option value="GD" {{ request('status') === 'GD' ? 'selected' : '' }}>GD</option>
                        <option value="QC" {{ request('status') === 'QC' ? 'selected' : '' }}>QC</option>
                        <option value="REJECT" {{ request('status') === 'REJECT' ? 'selected' : '' }}>REJECT</option>
                        <option value="AVL-NET" {{ request('status') === 'AVL-NET' ? 'selected' : '' }}>AVL-NET</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('activity.index') }}" class="px-4 py-2 bg-[#293031] text-slate-200 rounded font-semibold hover:bg-[#3c494a] transition text-sm">
                    <i class="bi bi-arrow-clockwise mr-2"></i>Reset
                </a>
                <a href="{{ route('activity.statistics') }}" class="px-4 py-2 bg-[#293031] text-slate-200 rounded font-semibold hover:bg-[#3c494a] transition text-sm ml-auto">
                    <i class="bi bi-bar-chart mr-2"></i>Statistics
                </a>
            </div>
        </form>
    </div>

    <!-- Activity Table -->
    <div class="overflow-hidden rounded-xl border border-[#293031] bg-[#171b1c]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#293031] bg-[#111516]">
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Date/Time</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">User ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Name</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Program</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Module</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Transaction</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Status</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#293031]">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-[#1e2324] transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-slate-300">{{ optional($activity->activity_date)->format('d M Y') ?: '-' }}</div>
                                <div class="text-xs text-slate-600">{{ $activity->activity_time ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($activity->user)
                                    <a href="{{ route('users.show', $activity->user->id) }}" class="font-semibold text-[#42d3c0] hover:text-[#52d5c5]">
                                        {{ $activity->user->user_id }}
                                    </a>
                                @else
                                    <span class="text-slate-500">Unmapped user</span>
                                    <div class="text-xs text-slate-700">ID: {{ $activity->user_id ?: '-' }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-200">
                                {{ $activity->user->user_name ?? 'Unmapped user' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-200">{{ optional($activity->program)->name ?: 'Unmapped program' }}</div>
                                <div class="text-xs text-slate-600">{{ optional($activity->program)->program_code ?: ($activity->program_id ?: '-') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if ($activity->program && $activity->program->module)
                                    <a href="{{ route('modules.show', $activity->program->module->id) }}" class="text-[#42d3c0] hover:text-[#52d5c5] font-semibold">
                                        {{ $activity->program->module->name }}
                                    </a>
                                @else
                                    <span class="text-slate-600">Unmapped module</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $activity->transaction_code ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if ($activity->status)
                                    <span class="inline-flex px-2 py-1 rounded text-xs font-semibold 
                                        @if ($activity->status === 'GD') bg-[#1e3c3a] text-[#2dd4bf]
                                        @elseif ($activity->status === 'QC') bg-[#3c2f1e] text-[#fbbf24]
                                        @elseif ($activity->status === 'REJECT') bg-[#3a2e2e] text-[#f87171]
                                        @else bg-[#2a3a3f] text-slate-300 @endif">
                                        {{ $activity->status }}
                                    </span>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button type="button"
                                    class="activity-view-button text-[#0f9f91] hover:text-[#42d3c0] font-semibold text-xs"
                                    data-date="{{ optional($activity->activity_date)->format('d M Y') ?: '-' }}"
                                    data-time="{{ $activity->activity_time ?? '-' }}"
                                    data-user="{{ $activity->user ? $activity->user->user_id . ' - ' . ($activity->user->user_name ?: '-') : 'Unmapped user' }}"
                                    data-program="{{ $activity->program ? ($activity->program->name ?: '-') : 'Unmapped program' }}"
                                    data-module="{{ $activity->program && $activity->program->module ? $activity->program->module->name : 'Unmapped module' }}"
                                    data-transaction="{{ $activity->transaction_code ?: '-' }}"
                                    data-type="{{ $activity->activity_type ?: '-' }}"
                                    data-status="{{ $activity->status ?: '-' }}"
                                    data-cost-center="{{ $activity->cost_center ?: '-' }}"
                                    data-end-user="{{ $activity->end_user ?: '-' }}">
                                    <i class="bi bi-eye-fill mr-1"></i>View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <i class="bi bi-inbox text-2xl mb-2 block"></i>
                                No activities found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($activities->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#293031] bg-[#111516] px-6 py-4">
                <p class="text-xs text-slate-500">
                    Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} results
                </p>
                <div class="flex items-center gap-1">
                    @if ($activities->onFirstPage())
                        <span class="rounded-lg border border-[#293031] px-3 py-2 text-xs text-slate-700">Previous</span>
                    @else
                        <a href="{{ $activities->previousPageUrl() }}" class="rounded-lg border border-[#3c494a] px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-[#0f9f91] hover:text-[#42d3c0]">Previous</a>
                    @endif
                    @foreach ($activities->getUrlRange(max(1, $activities->currentPage() - 2), min($activities->lastPage(), $activities->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="min-w-9 rounded-lg border px-3 py-2 text-center text-xs font-semibold transition {{ $page == $activities->currentPage() ? 'border-[#0f9f91] bg-[#0f9f91] text-white' : 'border-[#293031] text-slate-400 hover:border-[#0f9f91] hover:text-[#42d3c0]' }}">{{ $page }}</a>
                    @endforeach
                    @if ($activities->hasMorePages())
                        <a href="{{ $activities->nextPageUrl() }}" class="rounded-lg border border-[#3c494a] px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-[#0f9f91] hover:text-[#42d3c0]">Next</a>
                    @else
                        <span class="rounded-lg border border-[#293031] px-3 py-2 text-xs text-slate-700">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div id="activityModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="activityModalTitle">
    <div class="w-full max-w-2xl overflow-hidden rounded-xl border border-[#3c494a] bg-[#171b1c] shadow-2xl shadow-black/40">
        <div class="flex items-center justify-between border-b border-[#293031] px-6 py-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">Activity Detail</p>
                <h2 id="activityModalTitle" class="mt-1 text-lg font-bold text-slate-100">Transaction detail</h2>
            </div>
            <button type="button" data-modal-close class="shell-icon-button" aria-label="Close activity detail"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="grid gap-4 px-6 py-5 sm:grid-cols-2">
            @foreach ([
                'date' => 'Date', 'time' => 'Time', 'user' => 'User', 'program' => 'Program',
                'module' => 'Module', 'transaction' => 'Transaction', 'type' => 'Activity Type',
                'status' => 'Status', 'costCenter' => 'Cost Center', 'endUser' => 'End User',
            ] as $key => $label)
                <div class="rounded-lg border border-[#293031] bg-[#111516] px-4 py-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-600">{{ $label }}</p>
                    <p data-modal-value="{{ $key }}" class="mt-1 break-words text-sm font-semibold text-slate-200">-</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-auto-filter]').forEach((form) => {
        let timer;
        form.querySelectorAll('input, select').forEach((field) => {
            field.addEventListener(field.tagName === 'SELECT' ? 'change' : 'input', () => {
                clearTimeout(timer);
                timer = setTimeout(() => form.requestSubmit(), 350);
            });
        });
    });
</script>
@endpush
@endsection

@push('scripts')
<script>
    (() => {
        const modal = document.getElementById('activityModal');
        if (!modal) return;

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.querySelectorAll('.activity-view-button').forEach((button) => {
            button.addEventListener('click', () => {
                Object.keys(button.dataset).forEach((key) => {
                    const value = modal.querySelector(`[data-modal-value="${key}"]`);
                    if (value) value.textContent = button.dataset[key] || '-';
                });
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.closest('[data-modal-close]')) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeModal();
        });
    })();
</script>
@endpush

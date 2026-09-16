@extends('layouts.app')

@section('title', 'User Detail | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ $backUrl }}" class="mb-2 inline-flex items-center text-sm font-semibold text-[#42d3c0] hover:text-[#52d5c5]">
                <i class="bi bi-chevron-left mr-1"></i>{{ $backLabel }}
            </a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">{{ $eyebrow }}</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-100 sm:text-3xl">{{ $user->user_name ?: $user->user_id }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $user->user_id }} · {{ $user->description ?: 'No description' }}</p>
        </div>
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $user->active === 'yes' ? 'bg-[#1e3c3a] text-[#2dd4bf]' : 'bg-[#3a2e2e] text-[#f87171]' }}">
            {{ $user->active === 'yes' ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <div class="grid gap-6 lg:grid-cols-[.8fr_1.2fr]">
        <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
            <h2 class="mb-5 text-lg font-bold text-slate-100">User Profile</h2>
            <dl class="space-y-4 text-sm">
                <div class="flex justify-between gap-4 border-b border-[#293031] pb-3"><dt class="text-slate-500">User ID</dt><dd class="font-semibold text-slate-200">{{ $user->user_id }}</dd></div>
                <div class="flex justify-between gap-4 border-b border-[#293031] pb-3"><dt class="text-slate-500">Name</dt><dd class="text-right text-slate-200">{{ $user->user_name ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4 border-b border-[#293031] pb-3"><dt class="text-slate-500">Description</dt><dd class="text-right text-slate-200">{{ $user->description ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4 border-b border-[#293031] pb-3"><dt class="text-slate-500">Last Logon</dt><dd class="text-slate-200">{{ optional($user->last_logon)->format('d M Y') ?: '-' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd class="text-slate-200">{{ $user->active === 'yes' ? 'Active' : 'Inactive' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-100">Activity Summary</h2>
                    <p class="mt-1 text-sm text-slate-500">All activity linked through qad_activity_logs.user_id.</p>
                </div>
                <i class="bi bi-activity text-[#0f9f91]"></i>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-[#293031] bg-[#111516] p-4"><p class="text-xs uppercase text-slate-600">Activities</p><p class="mt-2 text-2xl font-bold text-slate-100">{{ number_format($summary['total_activities'] ?? 0) }}</p></div>
                <div class="rounded-lg border border-[#293031] bg-[#111516] p-4"><p class="text-xs uppercase text-slate-600">Modules</p><p class="mt-2 text-2xl font-bold text-slate-100">{{ $modules->count() }}</p></div>
                <div class="rounded-lg border border-[#293031] bg-[#111516] p-4"><p class="text-xs uppercase text-slate-600">Programs</p><p class="mt-2 text-2xl font-bold text-slate-100">{{ $programs->count() }}</p></div>
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
            <h2 class="mb-4 text-lg font-bold text-slate-100">Modules Used</h2>
            <div class="flex flex-wrap gap-2">
                @forelse ($modules as $module)
                    <a href="{{ route('modules.show', $module->id) }}" class="rounded bg-[#243233] px-3 py-2 text-sm font-semibold text-[#42d3c0] hover:bg-[#314142]">{{ $module->name }}</a>
                @empty
                    <p class="text-sm text-slate-500">No module activity found.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
            <h2 class="mb-4 text-lg font-bold text-slate-100">Programs Used</h2>
            <div class="space-y-2">
                @forelse ($programs as $program)
                    <div class="flex items-center justify-between gap-3 border-b border-[#293031] pb-2 last:border-0">
                        <div><p class="font-semibold text-slate-200">{{ $program->name ?: '-' }}</p><p class="text-xs text-slate-600">{{ $program->program_code ?: $program->address ?: '-' }}</p></div>
                        <span class="text-xs text-slate-400">{{ number_format($program->activity_count ?? 0) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No program activity found.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="overflow-hidden rounded-xl border border-[#293031] bg-[#171b1c]">
        <div class="border-b border-[#293031] p-6"><h2 class="text-lg font-bold text-slate-100">Recent Activity</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-[#293031] bg-[#111516]"><th class="px-6 py-3 text-left text-slate-400">Date</th><th class="px-6 py-3 text-left text-slate-400">Program</th><th class="px-6 py-3 text-left text-slate-400">Transaction</th><th class="px-6 py-3 text-left text-slate-400">Status</th></tr></thead>
                <tbody class="divide-y divide-[#293031]">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-[#1e2324]"><td class="px-6 py-3 text-slate-300">{{ optional($activity->activity_date)->format('d M Y') ?: '-' }}</td><td class="px-6 py-3 text-slate-200">{{ optional($activity->program)->name ?: '-' }}</td><td class="px-6 py-3 text-slate-300">{{ $activity->transaction_code ?: '-' }}</td><td class="px-6 py-3 text-slate-300">{{ $activity->status ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-slate-500">No activity found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

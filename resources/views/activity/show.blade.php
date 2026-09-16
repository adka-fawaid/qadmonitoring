@extends('layouts.app')

@section('title', 'Activity Detail | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ $backUrl }}" class="inline-flex items-center text-[#42d3c0] hover:text-[#52d5c5] mb-2 text-sm font-semibold">
                <i class="bi bi-chevron-left mr-1"></i>{{ $backLabel }}
            </a>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">{{ $eyebrow }}</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-100 sm:text-3xl">Activity #{{ $activity->id }}</h1>
        </div>
    </div>

    <!-- Activity Details -->
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Transaction Info -->
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h2 class="text-lg font-bold text-slate-100 mb-4">Transaction Details</h2>
                <div class="grid gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Date</p>
                            <p class="text-slate-200 font-semibold">{{ optional($activity->activity_date)->format('d M Y') ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Time</p>
                            <p class="text-slate-200 font-semibold">{{ $activity->activity_time }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Transaction Code</p>
                            <p class="text-slate-200 font-semibold">{{ $activity->transaction_code ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Activity Type</p>
                            <p class="text-slate-200 font-semibold">{{ $activity->activity_type ?: '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Status</p>
                        <div>
                            @if ($activity->status)
                                <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold 
                                    @if ($activity->status === 'GD') bg-[#1e3c3a] text-[#2dd4bf]
                                    @elseif ($activity->status === 'QC') bg-[#3c2f1e] text-[#fbbf24]
                                    @elseif ($activity->status === 'REJECT') bg-[#3a2e2e] text-[#f87171]
                                    @else bg-[#2a3a3f] text-slate-300 @endif">
                                    {{ $activity->status }}
                                </span>
                            @else
                                <span class="text-slate-600">-</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h2 class="text-lg font-bold text-slate-100 mb-4">Additional Information</h2>
                <div class="grid gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Cost Center</p>
                            <p class="text-slate-200">{{ $activity->cost_center ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">End User</p>
                            <p class="text-slate-200">{{ $activity->end_user ?: '-' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Effective Date</p>
                            <p class="text-slate-200">{{ $activity->effective_date ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Last Activity</p>
                            <p class="text-slate-200">{{ $activity->last_activity ?: '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Promise</p>
                        <p class="text-slate-200">{{ $activity->promise ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h3 class="text-lg font-bold text-slate-100 mb-4">User</h3>
                <div class="space-y-3">
                    @if ($activity->user)
                    <a href="{{ route('users.show', $activity->user->id) }}" class="block">
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">User ID</p>
                        <p class="text-slate-200 font-semibold text-[#42d3c0] hover:text-[#52d5c5]">{{ $activity->user->user_id }}</p>
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Name</p>
                        <p class="text-slate-200">{{ $activity->user->user_name ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Description</p>
                        <p class="text-slate-200">{{ $activity->user->description ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Status</p>
                        <p class="text-slate-200 text-sm">{{ $activity->user->active === 'yes' ? 'Active' : 'Inactive' }}</p>
                    </div>
                    @else
                        <p class="text-sm text-slate-500">Unmapped user (ID: {{ $activity->user_id ?: '-' }})</p>
                    @endif
                </div>
            </div>

            <!-- Program Info -->
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h3 class="text-lg font-bold text-slate-100 mb-4">Program</h3>
                @if ($activity->program)
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Name</p>
                        <p class="text-slate-200 font-semibold">{{ $activity->program->name ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Program Code</p>
                        <p class="text-slate-200">{{ $activity->program->program_code ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Address</p>
                        <p class="text-slate-200">{{ $activity->program->address ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Program Type</p>
                        <p class="text-slate-200">
                            <span class="inline-flex px-2 py-1 rounded text-xs font-semibold 
                                @if ($activity->program->program_type === 'Entryan') bg-[#3c2f1e] text-[#fbbf24]
                                @elseif ($activity->program->program_type === 'Laporan') bg-[#1e3c3a] text-[#2dd4bf]
                                @else bg-[#2a3a3f] text-slate-300 @endif">
                                {{ $activity->program->program_type ?: '-' }}
                            </span>
                        </p>
                    </div>
                </div>
                @else
                    <p class="text-sm text-slate-500">Unmapped program (ID: {{ $activity->program_id ?: '-' }})</p>
                @endif
            </div>

            <!-- Module Info -->
            <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-6">
                <h3 class="text-lg font-bold text-slate-100 mb-4">Module</h3>
                @if ($activity->program && $activity->program->module)
                <a href="{{ route('modules.show', $activity->program->module->id) }}" class="block">
                    <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Name</p>
                    <p class="text-slate-200 font-semibold text-[#42d3c0] hover:text-[#52d5c5]">{{ $activity->program->module->name ?? '-' }}</p>
                </a>
                @else
                    <p class="text-sm text-slate-500">Unmapped module</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

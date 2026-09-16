@extends('layouts.app')

@section('title', 'Users | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">Users</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-100 sm:text-3xl">User Directory</h1>
            <p class="mt-1 text-sm text-slate-500">QAD system users and their activity summary.</p>
            <p class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#293031] bg-[#171b1c] px-3 py-2 text-xs font-semibold text-slate-400"><i class="bi bi-calendar-range text-[#0f9f91]"></i>Rentang data: {{ $dataDateRange['from']?->format('d M Y') ?: '-' }} sampai {{ $dataDateRange['to']?->format('d M Y') ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] px-4 py-2 text-sm text-slate-400">
            <i class="bi bi-people-fill mr-2 text-[#0f9f91]"></i>
            <span>{{ $users->total() }} users</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-xl border border-[#293031] bg-[#171b1c] p-5 shadow-lg shadow-black/10">
        <form method="GET" action="{{ route('users.index') }}" class="grid gap-4 lg:grid-cols-[1.3fr_1.1fr_1fr_auto] lg:items-end" data-auto-filter>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="User ID or Name" 
                       class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm placeholder-slate-600 focus:outline-none focus:border-[#0f9f91]">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Description</label>
                <input type="text" name="description" value="{{ request('description') }}" placeholder="Description" 
                       class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm placeholder-slate-600 focus:outline-none focus:border-[#0f9f91]">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-500 mb-2">Status</label>
                <select name="active" class="w-full px-3 py-2 bg-[#111516] border border-[#293031] rounded text-slate-200 text-sm focus:outline-none focus:border-[#0f9f91]">
                    <option value="">All Status</option>
                    <option value="yes" {{ request('active') === 'yes' ? 'selected' : '' }}>Active</option>
                    <option value="no" {{ request('active') === 'no' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <a href="{{ route('users.index') }}" class="inline-flex items-center rounded-lg bg-[#293031] px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-[#3c494a]">
                    <i class="bi bi-arrow-clockwise mr-2"></i>Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="overflow-hidden rounded-xl border border-[#293031] bg-[#171b1c] shadow-lg shadow-black/10">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#293031] bg-[#151a1b]">
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">User ID</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Name</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-300">Description</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Status</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Activity</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Modules</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Programs</th>
                        <th class="px-6 py-4 text-center font-semibold text-slate-300">Last Logon</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#293031]">
                    @forelse ($users as $user)
                        <tr class="hover:bg-[#1e2324] transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('users.show', $user->id) }}" class="font-semibold text-[#42d3c0] hover:text-[#52d5c5]">
                                    {{ $user->user_id }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-slate-200">{{ $user->user_name ?: '-' }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $user->description ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if ($user->active === 'yes')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-[#1e3c3a] text-[#2dd4bf]">
                                        <span class="w-2 h-2 bg-[#2dd4bf] rounded-full mr-2"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-[#3a2e2e] text-[#f87171]">
                                        <span class="w-2 h-2 bg-[#f87171] rounded-full mr-2"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-slate-300">{{ number_format($user->activity_count ?? 0) }}</td>
                            <td class="px-6 py-4 text-center text-slate-300">{{ $user->unique_modules ?? 0 }}</td>
                            <td class="px-6 py-4 text-center text-slate-300">{{ $user->unique_programs ?? 0 }}</td>
                            <td class="px-6 py-4 text-center text-slate-400">{{ optional($user->last_logon)->format('d M Y') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500">
                                <i class="bi bi-inbox text-2xl mb-2 block"></i>
                                No users found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[#293031] bg-[#111516] px-6 py-4">
                <p class="text-xs text-slate-500">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                </p>
                <div class="flex items-center gap-1">
                    @if ($users->onFirstPage())
                        <span class="rounded-lg border border-[#293031] px-3 py-2 text-xs text-slate-700">Previous</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="rounded-lg border border-[#3c494a] px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-[#0f9f91] hover:text-[#42d3c0]">Previous</a>
                    @endif

                    @foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page => $url)
                        <a href="{{ $url }}" class="min-w-9 rounded-lg border px-3 py-2 text-center text-xs font-semibold transition {{ $page == $users->currentPage() ? 'border-[#0f9f91] bg-[#0f9f91] text-white' : 'border-[#293031] text-slate-400 hover:border-[#0f9f91] hover:text-[#42d3c0]' }}">{{ $page }}</a>
                    @endforeach

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="rounded-lg border border-[#3c494a] px-3 py-2 text-xs font-semibold text-slate-300 transition hover:border-[#0f9f91] hover:text-[#42d3c0]">Next</a>
                    @else
                        <span class="rounded-lg border border-[#293031] px-3 py-2 text-xs text-slate-700">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

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

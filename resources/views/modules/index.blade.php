@extends('layouts.app')

@section('title', 'Modules | QAD Monitoring')

@section('content')
<div class="mx-auto max-w-[1500px] space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="mb-1 text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">Modules</p>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-100 sm:text-2xl">QAD Modules</h1>
            <p class="mt-1 text-sm text-slate-500">All registered modules and their recorded usage.</p>
            <p class="mt-2 inline-flex items-center gap-2 rounded-lg border border-[#293031] bg-[#171b1c] px-3 py-2 text-xs font-semibold text-slate-400"><i class="bi bi-calendar-range text-[#0f9f91]"></i>Rentang data: {{ $dataDateRange['from']?->format('d M Y') ?: '-' }} sampai {{ $dataDateRange['to']?->format('d M Y') ?: '-' }}</p>
        </div>
        <div class="rounded-lg border border-[#293031] bg-[#171b1c] px-4 py-2 text-sm text-slate-400">
            <i class="bi bi-boxes mr-2 text-[#0f9f91]"></i>{{ $modules->count() }} modules
        </div>
    </div>

    <section class="overflow-hidden rounded-xl border border-[#293031] bg-[#171b1c] shadow-lg shadow-black/10">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#293031] bg-[#151a1b]">
                        <th class="px-5 py-4 text-left font-semibold text-slate-300">Module</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-300">Programs</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-300">Users</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-300">Activities</th>
                        <th class="px-5 py-4 text-center font-semibold text-slate-300">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#293031]">
                    @forelse ($modules as $module)
                        <tr class="transition hover:bg-[#1e2324]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#203233] text-[#42d3c0]"><i class="bi bi-box-seam"></i></span>
                                    <div><p class="font-semibold text-slate-100">{{ $module->name }}</p><p class="text-xs text-slate-600">{{ $module->normalized_name }}</p></div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ number_format($module->programs_count ?? 0) }}</td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ number_format($module->unique_users ?? 0) }}</td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ number_format($module->activity_count ?? 0) }}</td>
                            <td class="px-5 py-4 text-center">
                                <button type="button" class="module-view-button text-xs font-semibold text-[#42d3c0] hover:text-[#5ee7df]" data-module-id="{{ $module->id }}" data-module-name="{{ $module->name }}" data-programs="{{ $module->programs_count ?? 0 }}" data-users="{{ $module->unique_users ?? 0 }}" data-activities="{{ $module->activity_count ?? 0 }}" data-program-list='@json($module->programs)'>
                                    <i class="bi bi-eye-fill mr-1"></i>View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">No modules available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div id="moduleModal" class="module-modal fixed inset-0 z-50 hidden items-center justify-center bg-black/70 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="moduleModalTitle">
    <div class="module-modal-card w-full max-w-2xl overflow-hidden rounded-xl border border-[#3c494a] bg-[#171b1c] shadow-2xl shadow-black/40">
        <div class="flex items-center justify-between border-b border-[#293031] px-6 py-4">
            <div><p class="text-xs font-bold uppercase tracking-[.18em] text-[#0f9f91]">Module Detail</p><h2 id="moduleModalTitle" class="mt-1 text-lg font-bold text-slate-100">-</h2></div>
            <button type="button" data-modal-close class="shell-icon-button" aria-label="Close module detail"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="grid gap-3 px-6 py-5 sm:grid-cols-3">
            <div class="rounded-lg border border-[#293031] bg-[#111516] p-4"><p class="text-xs uppercase text-slate-600">Programs</p><p id="moduleModalPrograms" class="mt-2 text-2xl font-bold text-slate-100">0</p></div>
            <div class="rounded-lg border border-[#293031] bg-[#111516] p-4"><p class="text-xs uppercase text-slate-600">Users</p><p id="moduleModalUsers" class="mt-2 text-2xl font-bold text-slate-100">0</p></div>
            <div class="rounded-lg border border-[#293031] bg-[#111516] p-4"><p class="text-xs uppercase text-slate-600">Activities</p><p id="moduleModalActivities" class="mt-2 text-2xl font-bold text-slate-100">0</p></div>
        </div>
        <div class="module-modal-body px-6 pb-5"><h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Related Programs</h3><div id="moduleModalProgramList" class="module-modal-list space-y-2 overflow-y-auto"></div></div>
        <div class="flex justify-end gap-2 border-t border-[#293031] px-6 py-4"><button type="button" data-modal-close class="rounded-lg bg-[#293031] px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-[#3c494a]">Close</button><a id="moduleModalDetail" href="#" class="rounded-lg bg-[#0f9f91] px-4 py-2 text-sm font-semibold text-white hover:bg-[#42d3c0]">Open Detail</a></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('moduleModal');
    if (!modal) return;
    const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
    document.querySelectorAll('.module-view-button').forEach(button => button.addEventListener('click', () => {
        const programs = JSON.parse(button.dataset.programList || '[]');
        document.getElementById('moduleModalTitle').textContent = button.dataset.moduleName;
        document.getElementById('moduleModalPrograms').textContent = Number(button.dataset.programs).toLocaleString();
        document.getElementById('moduleModalUsers').textContent = Number(button.dataset.users).toLocaleString();
        document.getElementById('moduleModalActivities').textContent = Number(button.dataset.activities).toLocaleString();
        document.getElementById('moduleModalDetail').href = `{{ url('/modules') }}/${button.dataset.moduleId}`;
        document.getElementById('moduleModalProgramList').innerHTML = programs.length ? programs.map(program => `<div class="flex items-center justify-between gap-3 rounded-lg border border-[#293031] bg-[#111516] px-3 py-2"><div><p class="text-sm font-semibold text-slate-200">${program.name || '-'}</p><p class="text-xs text-slate-600">${program.program_code || program.address || '-'}</p></div><span class="text-xs text-slate-500">${program.program_type || '-'}</span></div>`).join('') : '<p class="text-sm text-slate-500">No related programs.</p>';
        modal.classList.remove('hidden'); modal.classList.add('flex');
    }));
    modal.addEventListener('click', event => { if (event.target === modal || event.target.closest('[data-modal-close]')) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
})();
</script>
@endpush

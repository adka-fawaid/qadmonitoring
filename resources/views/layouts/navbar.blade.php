<header class="sticky top-0 z-20 border-b border-[#292f30] bg-[#171b1c]/95 px-4 py-3 backdrop-blur sm:px-6">
	<div class="flex h-12 items-center justify-between gap-4">
		<div class="flex min-w-0 items-center gap-3">
			<button type="button" id="mobileSidebarToggle" class="shell-icon-button lg:hidden" aria-label="Open navigation">
				<i class="bi bi-list text-xl"></i>
			</button>
			<div class="min-w-0">
				<p class="truncate text-sm font-semibold text-slate-200">Operations overview</p>
				<p class="hidden text-xs text-slate-500 sm:block">Real-time system activity</p>
			</div>
		</div>

		<div class="flex items-center gap-1 sm:gap-2">
			<button type="button" id="themeToggle" class="shell-icon-button" aria-label="Toggle light mode" title="Toggle light mode"><i class="bi bi-sun"></i></button>
			<button type="button" id="globalSearchToggle" class="shell-icon-button" aria-label="Search users" title="Search users"><i class="bi bi-search"></i></button>
			<button type="button" class="shell-icon-button relative" aria-label="Notifications"><i class="bi bi-bell"></i><span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-orange-500"></span></button>
			<div class="ml-2 hidden h-8 w-px bg-[#303637] sm:block"></div>
			<button type="button" class="ml-1 flex items-center gap-2 rounded-full p-1 transition hover:bg-[#252b2c]" aria-label="Account menu">
				<span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0f766e] text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'QA', 0, 2)) }}</span>
				<i class="bi bi-chevron-down hidden text-xs text-slate-500 sm:block"></i>
			</button>
		</div>
	</div>
</header>

<div id="globalSearchPanel" class="global-search-panel hidden" role="dialog" aria-label="Search users">
	<div class="global-search-box">
		<form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-3">
			<i class="bi bi-search text-[#0f9f91]"></i>
			<input id="globalSearchInput" type="search" name="search" placeholder="Search user ID or name..." autocomplete="off">
			<button type="button" id="globalSearchClose" class="shell-icon-button" aria-label="Close search"><i class="bi bi-x-lg"></i></button>
		</form>
	</div>
</div>

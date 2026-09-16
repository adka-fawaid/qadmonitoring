<aside id="sidebar" class="sticky top-0 z-40 flex h-screen w-[260px] shrink-0 flex-col border-r border-[#292929] bg-[#171717] text-slate-300 transition-[width] duration-200">

    {{-- Header --}}
    <div class="sidebar-brand flex h-[72px] shrink-0 items-center justify-between border-b border-[#292929] px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#0f766e] text-white">
                <i class="bi bi-diagram-3-fill text-lg"></i>
            </div>
            <div class="leading-tight">
                <div class="sidebar-label text-[20px] font-semibold tracking-tight text-slate-200">Utility QAD</div>
                <div class="sidebar-label text-[13px] font-medium text-slate-400">Operations</div>
            </div>
        </a>

        <button type="button" id="sidebarToggle" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-[#242424] hover:text-white">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav flex-1 overflow-y-auto px-4 py-5 scrollbar-thin scrollbar-thumb-[#333] scrollbar-track-transparent">

        {{-- Main --}}
        <div class="mb-7">
            <div class="mb-3 px-2 text-[13px] font-semibold uppercase tracking-wider text-slate-500">Main</div>

            <div class="space-y-1">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-[#b8c4d8] text-[#171717]' : 'text-slate-300 hover:bg-[#222] hover:text-white' }} flex h-11 items-center justify-between rounded-lg px-3 transition">
                    <span class="flex items-center gap-3">
                        <i class="bi bi-grid text-[17px]"></i>
                        <span class="text-[15px] font-medium">Dashboard</span>
                    </span>
                    <i class="bi bi-chevron-right text-xs"></i>
                </a>

                {{-- Users --}}
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-[#b8c4d8] text-[#171717]' : 'text-slate-300 hover:bg-[#222] hover:text-white' }} flex h-11 items-center rounded-lg px-3 transition">
                    <span class="flex items-center gap-3">
                        <i class="bi bi-people text-[17px]"></i>
                        <span class="text-[15px] font-medium">Users</span>
                    </span>
                </a>

                <a href="{{ route('modules.index') }}" class="{{ request()->routeIs('modules.*') ? 'bg-[#b8c4d8] text-[#171717]' : 'text-slate-300 hover:bg-[#222] hover:text-white' }} flex h-11 items-center rounded-lg px-3 transition">
                    <span class="flex items-center gap-3">
                        <i class="bi bi-boxes text-[17px]"></i>
                        <span class="text-[15px] font-medium">Modules</span>
                    </span>
                </a>

                {{-- Activity --}}
                <a href="{{ route('activity.index') }}" class="{{ request()->routeIs('activity.*') ? 'bg-[#b8c4d8] text-[#171717]' : 'text-slate-300 hover:bg-[#222] hover:text-white' }} flex h-11 items-center rounded-lg px-3 transition">
                    <span class="flex items-center gap-3">
                        <i class="bi bi-activity text-[17px]"></i>
                        <span class="text-[15px] font-medium">Activity</span>
                    </span>
                </a>

            </div>
        </div>

        {{-- QAD Modules --}}
        <div>
            <div class="mb-3 px-2 text-[13px] font-semibold uppercase tracking-wider text-slate-500">QAD Modules</div>
            <div class="space-y-1">
                {{-- Sales --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="sales-menu">
                        <span class="flex items-center gap-3">  
                            <i class="bi bi-cart3 text-[17px]"></i>
                            <span class="text-[15px] font-medium">Sales</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="sales-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Sales Quotations</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Sales Orders / Invoicing</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Pricing</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Forecast / MPS</a>
                    </div>
                </div>

                {{-- Procurement --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="procurement-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-bag-check text-[17px]"></i>
                            <span class="text-[15px] font-medium">Procurement</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="procurement-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Purchasing</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Supplier Performance</a>
                    </div>
                </div>

                {{-- LPP --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="lpp-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-diagram-2 text-[17px]"></i>
                            <span class="text-[15px] font-medium">LPP</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="lpp-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">GRS – Global Requisition</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Material Requirement Planning</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Capacity Requirement Planning</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Work Order</a>
                    </div>
                </div>
                {{-- TI --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="ti-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-cpu text-[17px]"></i>
                            <span class="text-[15px] font-medium">TI</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="ti-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">QXtend</a>
                    </div>
                </div>
                {{-- R&D --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="rnd-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-beaker text-[17px]"></i>
                            <span class="text-[15px] font-medium">R&D</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="rnd-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Product Structure</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Formula / Process</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Routing / Work Center</a>
                    </div>
                </div>

                {{-- QC --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="qc-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-check2-circle text-[17px]"></i>
                            <span class="text-[15px] font-medium">QC</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="qc-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Quality Management</a>
                    </div>
                </div>

                {{-- Manufacture --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="manufacture-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-gear text-[17px]"></i>
                            <span class="text-[15px] font-medium">Manufacture</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="manufacture-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Shop Floor Control</a>
                    </div>
                </div>

                {{-- Warehouse --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="warehouse-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-box-seam text-[17px]"></i>
                            <span class="text-[15px] font-medium">Warehouse</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="warehouse-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Inventory Control</a>
                    </div>
                </div>

                {{-- Financial --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="financial-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-cash-stack text-[17px]"></i>
                            <span class="text-[15px] font-medium">Financial</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="financial-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Banking / Cash Management</a>
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Tax Management</a>
                    </div>
                </div>

                {{-- Accounting --}}
                <div>
                    <button type="button" class="module-toggle flex h-11 w-full items-center justify-between rounded-lg px-3 text-slate-300 transition hover:bg-[#222] hover:text-white" data-target="accounting-menu">
                        <span class="flex items-center gap-3">
                            <i class="bi bi-calculator text-[17px]"></i>
                            <span class="text-[15px] font-medium">Accounting</span>
                        </span>
                        <i class="bi bi-chevron-right module-chevron text-xs transition-transform"></i>
                    </button>

                    <div id="accounting-menu" class="hidden space-y-1 pl-10 pr-1 pt-1">
                        <a href="#" class="block rounded-md px-3 py-2 text-[13px] text-slate-400 transition hover:bg-[#222] hover:text-white">Fixed Asset</a>
                    </div>
                </div>

            </div>
        </div>
    </nav>
</aside>
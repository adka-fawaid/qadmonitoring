document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.module-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.dataset.target);
            const chevron = button.querySelector('.module-chevron');

            if (!target) return;

            target.classList.toggle('hidden');
            chevron.classList.toggle('rotate-90');
        });
    });

    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');

    if (sidebar && toggle) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('is-collapsed');
            document.body.classList.toggle('sidebar-collapsed', sidebar.classList.contains('is-collapsed'));
        });

        sidebar.querySelector('.sidebar-brand > a')?.addEventListener('click', (event) => {
            if (!sidebar.classList.contains('is-collapsed')) return;

            event.preventDefault();
            sidebar.classList.remove('is-collapsed');
            document.body.classList.remove('sidebar-collapsed');
        });
    }

    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    const closeMobileSidebar = () => {
        sidebar?.classList.remove('is-open');
        backdrop?.classList.add('hidden');
    };

    mobileToggle?.addEventListener('click', () => {
        sidebar?.classList.add('is-open');
        backdrop?.classList.remove('hidden');
    });

    backdrop?.addEventListener('click', closeMobileSidebar);
});
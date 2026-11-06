const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('sidebar-backdrop');
const toggles = document.querySelectorAll('[data-sidebar-toggle]');

const setSidebarOpen = (open) => {
    sidebar?.classList.toggle('-translate-x-full', !open);
    backdrop?.classList.toggle('hidden', !open);
    toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(open)));
};

toggles.forEach((toggle) => {
    toggle.addEventListener('click', () => {
        setSidebarOpen(sidebar.classList.contains('-translate-x-full'));
    });
});

backdrop?.addEventListener('click', () => setSidebarOpen(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebarOpen(false);
    }
});

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('sidebar', {
    isExpanded: localStorage.getItem('mixu.sidebar') !== 'collapsed',
    isMobileOpen: false,
    toggleExpanded() {
        this.isExpanded = !this.isExpanded;
        localStorage.setItem('mixu.sidebar', this.isExpanded ? 'expanded' : 'collapsed');
    },
    toggleMobile() {
        this.isMobileOpen = !this.isMobileOpen;
    },
    closeMobile() {
        this.isMobileOpen = false;
    },
});

Alpine.store('theme', {
    toggle() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('mixu.theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
    },
});

if (localStorage.getItem('mixu.theme') === 'light') {
    document.documentElement.classList.remove('dark');
} else if (localStorage.getItem('mixu.theme') === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.classList.add('dark');
}

Alpine.start();

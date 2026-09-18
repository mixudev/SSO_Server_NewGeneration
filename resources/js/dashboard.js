import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* =========================================================================
   Dashboard Shell — Sidebar, Mobile, Theme
   ========================================================================= */
Alpine.data('dashboardShell', () => ({
    sidebarExpanded: localStorage.getItem('dashboard.sidebar') !== 'collapsed',
    mobileOpen: false,
    isDark: document.documentElement.classList.contains('dark'),

    init() {
        // Sync isDark if theme toggled elsewhere (e.g., a different tab)
        const obs = new MutationObserver(() => {
            this.isDark = document.documentElement.classList.contains('dark');
        });
        obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        // Close mobile drawer on large screen resize
        const onResize = () => {
            if (window.innerWidth >= 1024 && this.mobileOpen) this.closeMobile();
        };
        window.addEventListener('resize', onResize);
        this.$cleanup = () => window.removeEventListener('resize', onResize);

        // Sync collapsed class on sidebarExpanded change (for zero-flicker)
        this.$watch('sidebarExpanded', (val) => {
            if (!val) {
                document.documentElement.classList.add('sidebar-is-collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-is-collapsed');
            }
        });
    },

    toggleSidebar() {
        this.sidebarExpanded = !this.sidebarExpanded;
        localStorage.setItem('dashboard.sidebar', this.sidebarExpanded ? 'expanded' : 'collapsed');
    },

    openMobile() {
        this.mobileOpen = true;
        document.body.style.overflow = 'hidden';
    },

    closeMobile() {
        this.mobileOpen = false;
        document.body.style.overflow = '';
    },

    toggleTheme() {
        const willBeDark = !this.isDark;
        if (willBeDark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('dashboard.theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('dashboard.theme', 'light');
        }
        this.isDark = willBeDark;
    },
}));

/* =========================================================================
   Spotlight Command Palette
   ========================================================================= */
Alpine.data('dashboardSearch', () => ({
    openState: false,
    previousFocus: null,
    query: '',
    navItems: [
        { name: 'Dashboard Overview',         href: '/admin',          icon: 'bi bi-graph-up' },
        { name: 'OAuth2 / OIDC Client Apps',  href: '/admin/applications', icon: 'bi bi-key' },
        { name: 'Users & Roles',              href: '/admin/users',        icon: 'bi bi-people' },
        { name: 'Organizations & Tenants',    href: '/admin/organizations', icon: 'bi bi-building' },
        { name: 'Security Audit Logs',        href: '/admin/audit',        icon: 'bi bi-shield-lock' },
        { name: 'System Settings',            href: null,                  icon: 'bi bi-gear', disabled: true },
    ],
    actionItems: [
        { name: 'Rotate Security Keys',    icon: 'bi bi-arrow-repeat',       action: () => window.AppModal?.open('rotateKeyModal') },
        { name: 'Flush Identity Cache',    icon: 'bi bi-eraser',             action: () => window.confirmClearCache?.() },
        { name: 'Revoke All Active Sessions', icon: 'bi bi-slash-circle',    action: () => window.confirmRevokeTokens?.() },
    ],
    open() {
        this.openState = true;
        this.query = '';
        this.$nextTick(() => this.$refs.searchInput?.focus());
    },
    close() {
        this.openState = false;
        this.query = '';
    },
}));

/* =========================================================================
   Global Confirmation Helper
   ========================================================================= */
window.confirmAction = function({
    type        = 'confirm',
    title       = 'Confirm Action',
    description = 'Are you sure you want to proceed? This action cannot be undone.',
    confirmText = 'Yes, Proceed',
    cancelText  = 'Cancel',
    onConfirm   = null,
    onCancel    = null,
} = {}) {
    if (window.AppPopup && typeof window.AppPopup.confirm === 'function') {
        window.AppPopup.confirm({
            type, title, description, confirmText, cancelText,
            onConfirm: () => { if (typeof onConfirm === 'function') onConfirm(); },
            onCancel:  () => { if (typeof onCancel  === 'function') onCancel();  },
        });
    } else {
        console.error('AppPopup is unavailable; confirmation was not submitted.');
        if (typeof onCancel === 'function') onCancel();
    }
};

/* =========================================================================
   Declarative [data-confirm] Listener
   ========================================================================= */
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) return;
    event.preventDefault();
    event.stopPropagation();

    window.confirmAction({
        type:        trigger.getAttribute('data-confirm-type')  || 'confirm',
        title:       trigger.getAttribute('data-confirm-title') || 'Confirm Action',
        description: trigger.getAttribute('data-confirm')       || 'Are you sure you want to proceed?',
        confirmText: trigger.getAttribute('data-confirm-btn')   || 'Yes, Proceed',
        cancelText:  trigger.getAttribute('data-cancel-btn')    || 'Cancel',
        onConfirm: () => {
            const form = trigger.closest('form');
            if (form) form.submit();
            else if (trigger.tagName === 'A' && trigger.href) window.location.href = trigger.href;
        },
    });
});

/* =========================================================================
   Global Keyboard Shortcuts
   ========================================================================= */
document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        document.getElementById('search-palette-trigger')?.click();
    }
});

Alpine.start();

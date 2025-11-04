import './bootstrap';

// Conditionally load Alpine.js for homework pages only (to avoid conflicts with Bootstrap CRM)
if (window.location.pathname.startsWith('/homework')) {
    try {
        import('alpinejs').then(({ default: Alpine }) => {
            window.Alpine = Alpine;
            Alpine.start();
        }).catch(() => {
            // Alpine.js not available - this is okay, homework pages will work without it
            console.log('Alpine.js not loaded - homework functionality may be limited');
        });
    } catch (e) {
        // Alpine.js import failed - continue without it
    }
}

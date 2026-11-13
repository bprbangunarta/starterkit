// Applies the saved theme before the page paints, so a dark choice never flashes white. Loaded from resources/views/app.blade.php as a classic
// script (a strict CSP allows 'self' scripts only). The key matches THEME_KEY in resources/js/lib/theme.ts.
try {
    var saved = localStorage.getItem('theme');

    if (saved === 'light' || saved === 'dark') {
        document.documentElement.setAttribute('data-theme', saved);
    }
} catch (error) {
    // Storage blocked: the system setting decides.
}

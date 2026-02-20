function toggleTheme() {
    const root = document.documentElement;
    const currentTheme = root.getAttribute('data-theme');
    const newTheme = (currentTheme === 'light') ? 'dark' : 'light';
    
    root.setAttribute('data-theme', newTheme);
    localStorage.setItem('selected-theme', newTheme);
}

(function() {
    const savedTheme = localStorage.getItem('selected-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();
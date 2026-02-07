function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = (currentTheme === 'light') ? 'dark' : 'light';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('selected-theme', newTheme);
}

(function() {
    const savedTheme = localStorage.getItem('selected-theme') || 'dark';
    document.body.setAttribute('data-theme', savedTheme);
})();
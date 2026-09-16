import './sidebar';

const savedTheme = localStorage.getItem('qad-theme') || 'dark';
document.body.classList.toggle('light-theme', savedTheme === 'light');

document.addEventListener('DOMContentLoaded', () => {
	const body = document.body;
	const themeToggle = document.getElementById('themeToggle');
	const searchToggle = document.getElementById('globalSearchToggle');
	const searchPanel = document.getElementById('globalSearchPanel');
	const searchInput = document.getElementById('globalSearchInput');
	const searchClose = document.getElementById('globalSearchClose');

	const setTheme = (theme) => {
		body.classList.toggle('light-theme', theme === 'light');
		localStorage.setItem('qad-theme', theme);
		if (themeToggle) {
			themeToggle.innerHTML = `<i class="bi bi-${theme === 'light' ? 'moon' : 'sun'}"></i>`;
			themeToggle.setAttribute('aria-label', theme === 'light' ? 'Toggle dark mode' : 'Toggle light mode');
		}
	};

	setTheme(savedTheme);

	themeToggle?.addEventListener('click', () => {
		setTheme(body.classList.contains('light-theme') ? 'dark' : 'light');
	});

	const closeSearch = () => {
		searchPanel?.classList.add('hidden');
		searchInput?.blur();
	};

	searchToggle?.addEventListener('click', () => {
		searchPanel?.classList.remove('hidden');
		searchInput?.focus();
	});
	searchClose?.addEventListener('click', closeSearch);
	searchPanel?.addEventListener('click', (event) => {
		if (event.target === searchPanel) closeSearch();
	});
	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') closeSearch();
	});
});
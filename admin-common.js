import { auth } from './firebase-config.js';

auth.onAuthStateChanged(user => {
  if (!user) window.location.href = 'login.html';
});

const logoutButton = document.getElementById('logout');
if (logoutButton) logoutButton.addEventListener('click', () => auth.signOut());

const currentPage = document.body.dataset.adminPage;
document.querySelectorAll('.admin-menu a[data-page]').forEach(link => {
  link.classList.toggle('active', link.dataset.page === currentPage);
});

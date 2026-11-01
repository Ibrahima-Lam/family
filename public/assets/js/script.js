const menu = document.getElementById('menu');
const menuBar = document.getElementById('menu-bar');
const primaryNav = document.getElementById('link');
const mobileToggle = document.getElementById('list-link');

function closeActions() {
    menu?.classList.remove('is-open');
    menuBar?.setAttribute('aria-expanded', 'false');
}

function closeNavigation() {
    primaryNav?.classList.remove('is-open');
    mobileToggle?.setAttribute('aria-expanded', 'false');
}

menuBar?.addEventListener('click', function (event) {
    event.preventDefault();
    event.stopPropagation();
    closeNavigation();
    const open = menu.classList.toggle('is-open');
    menuBar.setAttribute('aria-expanded', open ? 'true' : 'false');
});

mobileToggle?.addEventListener('click', function (event) {
    event.preventDefault();
    event.stopPropagation();
    closeActions();
    const open = primaryNav.classList.toggle('is-open');
    mobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
});

document.addEventListener('click', function (event) {
    if (!menu?.contains(event.target) && !menuBar?.contains(event.target)) {
        closeActions();
    }
    if (!primaryNav?.contains(event.target) && !mobileToggle?.contains(event.target)) {
        closeNavigation();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeActions();
        closeNavigation();
    }
});

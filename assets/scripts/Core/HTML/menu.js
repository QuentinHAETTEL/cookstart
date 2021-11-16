export function toggleMenu()
{
    const menu = document.getElementById('menu');
    const menuToggle = document.getElementById('menu-toggle');

    if (menu && menuToggle) {
        menuToggle.addEventListener('click', function () {
            menu.classList.toggle('menu--open');
        });
    }

}
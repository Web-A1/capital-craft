(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var burger = document.querySelector('.burger');
        var mobileNav = document.querySelector('.mobile-nav');
        if (!burger || !mobileNav) return;

        burger.addEventListener('click', function () {
            burger.classList.toggle('active');
            mobileNav.classList.toggle('open');
            document.body.classList.toggle('menu-open');
        });
    });
})();
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var burger = document.querySelector('.burger');
        var mobileNav = document.querySelector('.mobile-nav');
        if (burger && mobileNav) {
            burger.addEventListener('click', function () {
                burger.classList.toggle('active');
                mobileNav.classList.toggle('open');
                document.body.classList.toggle('menu-open');
            });
        }

        var openBtn = document.querySelector('.js-open-modal');
        var modal = document.getElementById('contact-modal');
        var closeBtn = modal ? modal.querySelector('.modal__close') : null;
        var form = document.getElementById('contactForm');

        function openModal() {
            modal.classList.add('open');
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            modal.classList.remove('open');
            document.body.classList.remove('modal-open');
        }

        if (openBtn && modal) {
            openBtn.addEventListener('click', openModal);
        }
        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal || e.target.classList.contains('modal__overlay')) {
                    closeModal();
                }
            });
        }

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var phoneInput = form.querySelector('input[name="phone"]');
                var phone = phoneInput.value.trim();
                var error = form.querySelector('.form-error');
                var valid = /^\+?\d{10,15}$/.test(phone);
                if (!valid) {
                    error.style.display = 'block';
                    return;
                } else {
                    error.style.display = 'none';
                }

                var fd = new FormData(form);
                fetch('templates/capitalcraft/sendToTelegram.php', {
                    method: 'POST',
                    body: fd
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.status === 'ok') {
                            form.querySelector('.form-result.success').style.display = 'block';
                            form.querySelector('.form-result.error').style.display = 'none';
                            form.reset();
                        } else {
                            throw new Error();
                        }
                    })
                    .catch(function () {
                        form.querySelector('.form-result.error').style.display = 'block';
                        form.querySelector('.form-result.success').style.display = 'none';
                    });
                          });
        }
    });
})();

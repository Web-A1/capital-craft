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
    var phoneInput = form ? form.querySelector('input[name="phone"]') : null;

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
        if (
          e.target === modal ||
          e.target.classList.contains('modal__overlay')
        ) {
          closeModal();
        }
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('open')) {
          closeModal();
        }
      });
    }

    if (phoneInput) {
      phoneInput.addEventListener('input', function () {
        var digits = this.value.replace(/\D/g, '').replace(/^8/, '7');
        if (digits.charAt(0) !== '7') {
          digits = '7' + digits;
        }
        if (digits.length > 11) {
          digits = digits.slice(0, 11);
        }
        var formatted = '+7';
        if (digits.length > 1) {
          formatted += ' (' + digits.slice(1, 4);
          if (digits.length >= 4) formatted += ') ';
        }
        if (digits.length >= 4) {
          formatted += digits.slice(4, 7);
        }
        if (digits.length >= 7) {
          formatted += '-' + digits.slice(7, 9);
        }
        if (digits.length >= 9) {
          formatted += '-' + digits.slice(9, 11);
        }
        this.value = formatted;
      });
    }

    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var phone = phoneInput ? phoneInput.value.replace(/\D/g, '') : '';
        var error = form.querySelector('.form-error');
        var valid = phone.length === 11 && phone.startsWith('7');
        if (!valid) {
          error.style.display = 'block';
          return;
        } else {
          error.style.display = 'none';
        }

        var fd = new FormData(form);
        fetch('templates/capitalcraft/sendToTelegram.php', {
          method: 'POST',
          body: fd,
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            if (data.status === 'ok') {
              form.querySelector('.form-result.success').style.display =
                'block';
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

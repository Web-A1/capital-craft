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
    var header = modal ? modal.querySelector('.modal__header') : null;
    var successBox = modal ? modal.querySelector('.modal__success') : null;
    var phoneInput = form ? form.querySelector('input[name="phone"]') : null;
    var consentInput = form ? form.querySelector('.personal-data input') : null;
    var consentError = form ? form.querySelector('.consent-error') : null;

    function openModal() {
      modal.classList.add('open');
      document.body.classList.add('modal-open');
      if (form) {
        form.style.display = 'flex';
        form.reset();
        var err = form.querySelector('.form-error');
        if (err) err.style.display = 'none';
        var consentErr = form.querySelector('.consent-error');
        if (consentErr) consentErr.style.display = 'none';
        var errorResult = form.querySelector('.form-result.error');
        if (errorResult) errorResult.style.display = 'none';
      }
      if (header) header.style.display = 'flex';
      if (successBox) successBox.style.display = 'none';
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
        var errEl = form ? form.querySelector('.form-error') : null;
        if (errEl) errEl.style.display = 'none';
      });
    }
    if (consentInput) {
      consentInput.addEventListener('change', function () {
        if (consentError) consentError.style.display = 'none';
      });
    }

    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var phone = phoneInput ? phoneInput.value.replace(/\D/g, '') : '';
        var error = form.querySelector('.form-error');
        var message = '';
        if (!phone) {
          message = 'Пожалуйста, введите номер телефона';
        } else if (phone.length !== 11 || !phone.startsWith('7')) {
          message = 'Введите корректный номер телефона';
        }
        var valid = !message;
        if (message) {
          error.textContent = message;
          error.style.display = 'block';
        } else {
          error.style.display = 'none';
        }

        var consentValid = !consentInput || consentInput.checked;
        if (!consentValid && consentError) {
          consentError.style.display = 'block';
        } else if (consentError) {
          consentError.style.display = 'none';
        }

        if (!valid || !consentValid) {
          return;
        }

        var fd = new FormData(form);
        fetch('templates/capitalcraft/send_to_telegram.php', {
          method: 'POST',
          body: fd,
        })
          .then(function (r) {
            return r.json();
          })
          .then(function (data) {
            if (data.status === 'ok') {
              var errorRes = form.querySelector('.form-result.error');
              if (errorRes) errorRes.style.display = 'none';
              form.style.display = 'none';
              if (header) header.style.display = 'none';
              if (successBox) successBox.style.display = 'flex';
            } else {
              throw new Error();
            }
          })
          .catch(function () {
            form.querySelector('.form-result.error').style.display = 'block';
            if (successBox) successBox.style.display = 'none';
          });
      });
    }

    var partnersList = document.querySelector('.partners__list');
    if (partnersList) {
      var slideTime = 500;
      setInterval(function () {
        var first = partnersList.children[0];
        var shift = first.offsetWidth + 40; // gap
        partnersList.style.transition = 'transform ' + slideTime + 'ms';
        partnersList.style.transform = 'translateX(-' + shift + 'px)';
        setTimeout(function () {
          partnersList.appendChild(first);
          partnersList.style.transition = 'none';
          partnersList.style.transform = 'translateX(0)';
        }, slideTime);
      }, 5000);
    }
  });
})();

<div class="modal" id="contact-modal" aria-hidden="true">
    <div class="modal__overlay"></div>
    <div class="modal__body">
        <button type="button" class="modal__close" aria-label="Закрыть">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <line x1="4" y1="4" x2="20" y2="20" />
                <line x1="20" y1="4" x2="4" y2="20" />
            </svg>
        </button>
        <div class="modal__header">
            <p class="modal__subtitle">обсудить проект</p>
            <h2 class="modal__title">Давайте разбираться</h2>
            <p class="modal__description">Оставьте свои контакты и кратко опишите задачу — мы свяжемся с вами, чтобы обсудить детали</p>
        </div>
        <form id="contactForm" class="modal__form">
            <input type="text" name="name" placeholder="Имя">
            <div class="phone-field">
                <input type="tel" name="phone" required placeholder="Контактный телефон">
                <span class="form-error">Введите корректный номер телефона</span>
            </div>
            <textarea name="message" placeholder="Опишите Вашу задачу"></textarea>
            <label class="personal-data">
                <input type="checkbox" required>
                <span>Соглашаюсь на обработку и передачу персональных данных</span>
            </label>
            <button type="submit" class="btn btn--primary modal__btn">
                <span>Отправить</span>
                <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 7.49969C0 7.12772 0.273914 6.81983 0.628906 6.77118L0.727539 6.76434L13.5117 6.76434L8.89355 2.11884C8.60893 1.83253 8.60823 1.36733 8.8916 1.07977C9.14925 0.818358 9.55271 0.793725 9.83789 1.00653L9.91992 1.07684L15.7861 6.97821C15.8531 7.04562 15.9029 7.12416 15.9385 7.20673C15.9438 7.21911 15.9475 7.23208 15.9521 7.24481C15.9641 7.27737 15.9752 7.30979 15.9824 7.34344C15.9846 7.35343 15.9856 7.36359 15.9873 7.37372C15.9936 7.41069 15.9973 7.44764 15.998 7.48505C15.9981 7.48989 16 7.49482 16 7.49969C16 7.50823 15.9974 7.51662 15.9971 7.52508C15.9961 7.5539 15.9946 7.58247 15.9902 7.61102C15.9874 7.62956 15.9836 7.64765 15.9795 7.66571C15.974 7.68944 15.9678 7.71283 15.96 7.73602C15.9532 7.75611 15.9449 7.77535 15.9365 7.79462C15.9286 7.81277 15.9206 7.83074 15.9111 7.84833C15.8993 7.87037 15.886 7.8912 15.8721 7.9118C15.8666 7.91985 15.8632 7.92932 15.8574 7.93719L15.834 7.96454C15.8248 7.97586 15.8155 7.987 15.8057 7.99774L15.7871 8.0202L9.91992 13.9225C9.63542 14.2088 9.17507 14.2078 8.8916 13.9206C8.63393 13.6592 8.61139 13.2508 8.82324 12.9636L8.89355 12.8815L13.5107 8.23407H0.727539C0.325904 8.23407 6.17158e-05 7.90543 0 7.49969Z" fill="currentColor" />
                </svg>
            </button>
            <div class="form-result success">Спасибо! Мы свяжемся с вами в ближайшее время.</div>
            <div class="form-result error">Ошибка отправки. Попробуйте позже.</div>
        </form>
    </div>
</div>

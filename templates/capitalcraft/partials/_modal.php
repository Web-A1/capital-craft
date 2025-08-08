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
        <form id="contactForm" class="modal__form" novalidate>
            <input type="text" name="name" placeholder="Имя">
            <div class="phone-field">
                <input type="tel" name="phone" placeholder="Контактный телефон">
                <span class="form-error">Введите корректный номер телефона</span>
            </div>
            <textarea name="message" placeholder="Опишите Вашу задачу"></textarea>
            <label class="personal-data">
                <input type="checkbox">
                <span>Соглашаюсь на обработку и передачу персональных данных</span>
            </label>
            <span class="consent-error">Необходимо дать согласие на обработку данных</span>
            <button type="submit" class="btn-main modal__btn">
                <span>Отправить</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
                </svg>
            </button>
            <div class="form-result error">Ошибка отправки. Попробуйте позже.</div>
        </form>
        <div class="modal__success">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 13l4 4L19 7" />
            </svg>
            <p>Спасибо, форма успешно отправлена! <br>Наш специалист скоро с Вами свяжется</p>
        </div>
    </div>
</div>
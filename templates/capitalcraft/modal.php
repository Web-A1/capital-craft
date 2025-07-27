<div class="modal" id="contact-modal" aria-hidden="true">
    <div class="modal__overlay"></div>
    <div class="modal__body">
        <button type="button" class="modal__close" aria-label="Закрыть">&times;</button>
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
                <input type="checkbox" required> Соглашаюсь на обработку и передачу персональных данных
            </label>
            <button type="submit" class="btn btn--primary">Отправить</button>
            <div class="form-result success">Спасибо! Мы свяжемся с вами в ближайшее время.</div>
            <div class="form-result error">Ошибка отправки. Попробуйте позже.</div>
        </form>
    </div>
</div>

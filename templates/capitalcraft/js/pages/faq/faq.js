document.addEventListener('DOMContentLoaded', function () {
    const items = document.querySelectorAll('.faq__item');

    items.forEach(function (item) {
        const question = item.querySelector('.faq__question');

        question.addEventListener('click', function () {
            const activeItem = document.querySelector('.faq__item.active');

            if (activeItem && activeItem !== item) {
                activeItem.classList.remove('active');
            }

            item.classList.toggle('active');
        });
    });
});

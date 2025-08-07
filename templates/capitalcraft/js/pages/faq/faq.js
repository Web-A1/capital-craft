document.addEventListener('DOMContentLoaded', function () {
    const questions = document.querySelectorAll('.faq__question');

    questions.forEach(function (question) {
        question.addEventListener('click', function () {
            const isExpanded = question.getAttribute('aria-expanded') === 'true';

            questions.forEach(function (q) {
                q.setAttribute('aria-expanded', 'false');
            });

            question.setAttribute('aria-expanded', String(!isExpanded));
        });
    });
});

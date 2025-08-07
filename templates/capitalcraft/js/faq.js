document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.faq__question').forEach(function (q) {
        q.addEventListener('click', function () {
            q.parentElement.classList.toggle('active');
        });
    });
});


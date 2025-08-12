document.addEventListener('DOMContentLoaded', function () {
  const questions = document.querySelectorAll('.faq__question');

  questions.forEach(function (question) {
    question.addEventListener('click', function () {
      const isExpanded = question.getAttribute('aria-expanded') === 'true';
      const answer = question.nextElementSibling;

      questions.forEach(function (q) {
        if (q !== question && q.getAttribute('aria-expanded') === 'true') {
          const otherAnswer = q.nextElementSibling;
          q.setAttribute('aria-expanded', 'false');
          if (otherAnswer) {
            otherAnswer.style.maxHeight = '0px';
            otherAnswer.addEventListener('transitionend', function handler(e) {
              if (
                e.propertyName === 'max-height' &&
                q.getAttribute('aria-expanded') === 'false'
              ) {
                otherAnswer.style.removeProperty('max-height');
                otherAnswer.removeEventListener('transitionend', handler);
              }
            });
          }
        }
      });

      if (!isExpanded) {
        question.setAttribute('aria-expanded', 'true');
        if (answer) {
          answer.style.maxHeight = answer.scrollHeight + 20 + 'px';
        }
      } else {
        question.setAttribute('aria-expanded', 'false');
        if (answer) {
          answer.style.maxHeight = '0px';
          answer.addEventListener('transitionend', function handler(e) {
            if (
              e.propertyName === 'max-height' &&
              question.getAttribute('aria-expanded') === 'false'
            ) {
              answer.style.removeProperty('max-height');
              answer.removeEventListener('transitionend', handler);
            }
          });
        }
      }
    });
  });
});

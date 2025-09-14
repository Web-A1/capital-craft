document.addEventListener('DOMContentLoaded', function () {
  const questions = document.querySelectorAll('.faq__question');

  function getAnswerForQuestion(question) {
    // Ответ — следующий элемент после кнопки (как на прод)
    return question.nextElementSibling;
  }

  function collapseQuestion(q) {
    const ans = getAnswerForQuestion(q);
    q.setAttribute('aria-expanded', 'false');
    if (!ans) return;
    ans.classList.remove('open');
    ans.style.maxHeight = '0px';
    ans.addEventListener('transitionend', function handler(e) {
      if (e.propertyName === 'max-height' && q.getAttribute('aria-expanded') === 'false') {
        ans.style.removeProperty('max-height');
        ans.removeEventListener('transitionend', handler);
      }
    });
  }

  function expandQuestion(q) {
    const ans = getAnswerForQuestion(q);
    q.setAttribute('aria-expanded', 'true');
    if (!ans) return;
    // force reflow then set height
    void ans.offsetHeight;
    ans.classList.add('open');
    ans.style.maxHeight = ans.scrollHeight + 20 + 'px';
  }

  questions.forEach(function (question) {
    question.addEventListener('click', function (e) {
      const isExpanded = question.getAttribute('aria-expanded') === 'true';

      // Закрываем другие
      questions.forEach(function (q) {
        if (q !== question && q.getAttribute('aria-expanded') === 'true') {
          collapseQuestion(q);
        }
      });

      if (isExpanded) {
        collapseQuestion(question);
      } else {
        expandQuestion(question);
      }
    });

    question.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        question.click();
      }
    });
  });
});

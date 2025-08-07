<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addStyleSheet('templates/capitalcraft/css/faq.css');
$doc->addScript('templates/capitalcraft/js/faq.js');
?>

<section class="faq">
    <div class="faq__container">
        <div class="faq__accordion">
            <div class="faq__item">
                <h3 class="faq__question">Вопрос?</h3>
                <div class="faq__answer">Ответ...</div>
            </div>
            <!-- свой кастомный HTML -->
        </div>
        <figure class="faq__image">
            <!-- картинка -->
        </figure>
    </div>
</section>
<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addStyleSheet('templates/capitalcraft/css/faq.css');

require __DIR__ . '/../../../data/faq.php';
?>

<section class="faq">
    <div class="faq__container">
        <div class="faq__content">
            <div class="faq__title-block">
                <div class="faq__subtitle">часто задаваемые вопросы</div>
                <h1 class="faq__title">Сильные решения начинаются с неудобных вопросов</h1>
            </div>
            <div class="faq__accordion">
                <?php foreach ($faq as $item): ?>
                    <div class="faq__item">
                        <h3 class="faq__question"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="faq__answer"><?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <figure class="faq__image">
            <img src="/templates/capitalcraft/images/faq/faq_hand.webp" alt="FAQ">
        </figure>
    </div>
</section>
<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addStyleSheet('templates/capitalcraft/css/faq.css');

require __DIR__ . '/../../../data/faq_data.php';
?>

<section class="faq frame section-with-divider">
    <div class="faq__container">
        <div class="faq__content">
            <div class="faq__title-block">
                <div class="faq__subtitle">часто задаваемые вопросы</div>
                <h1 class="faq__title">Сильные решения начинаются с вопросов</h1>
            </div>
            <div class="faq__accordion">
                <?php foreach ($faq_data as $item): ?>
                    <div class="faq__item">
                        <button class="faq__question" aria-expanded="false">
                            <span class="faq__text">
                                <?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="faq__icon">+</span>
                        </button>
                        <div class="faq__answer">
                            <?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <figure class="faq__image">
            <img src="/templates/capitalcraft/images/faq/faq_hand.webp" alt="FAQ" loading="lazy">
        </figure>
    </div>
</section>
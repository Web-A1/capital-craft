<?php
require __DIR__ . '/../../data/faq_data.php';
$faqHome   = array_slice($faq_data, 0, 9);
$faqGroups = array_chunk($faqHome, 3);
?>

<section class="faq frame section-with-divider" id="faq">
    <div class="container faq__inner">
        <div class="faq__swiper swiper">
            <div class="swiper-wrapper">
                <?php foreach ($faqGroups as $group): ?>
                    <div class="faq__slide swiper-slide">
                        <ul>
                            <?php foreach ($group as $item): ?>
                                <li class="faq__item"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="faq__pagination swiper-pagination"></div>
        </div>

        <div class="faq__content">
            <div class="faq__title-block">
                <div class="faq__subtitle">часто задаваемые вопросы</div>
                <h2 class="faq__title">Сильные решения начинаются с вопросов</h2>
            </div>
            <a href="/faq" class="btn-main faq__btn faq__btn--desktop">
                <span>вопросы - ответы</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
            </a>
        </div>
        <a href="/faq" class="btn-main faq__btn faq__btn--mobile">
            <span>вопросы - ответы</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
            </svg>
        </a>
    </div>
</section>
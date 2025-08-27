<?php
require __DIR__ . '/../../data/faq_data.php';
$faqHome = array_slice($faq_data, 0, 9);
$faqGroups = array_chunk($faqHome, 3);
?>

<section class="faq-home frame section-with-divider" id="faq">
    <div class="container faq-home__inner">
        <div class="faq-home__swiper swiper">
            <div class="swiper-wrapper">
                <?php foreach ($faqGroups as $group): ?>
                    <div class="faq-home__slide swiper-slide">
                        <ul>
                            <?php foreach ($group as $item): ?>
                                <li class="faq-home__item"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="faq-home__pagination swiper-pagination"></div>
        </div>

        <div class="faq-home__content">
            <div class="faq-home__title-block">
                <h2 class="faq-home__subtitle">Часто задаваемые вопросы</h2>
                <p class="faq-home__title">Сильные решения начинаются с вопросов</p>
            </div>
            <a href="/faq" class="btn-main faq-home__btn faq-home__btn--desktop">
                <span>Вопросы - ответы</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
                </svg>
            </a>
        </div>
        <a href="/faq" class="btn-main faq-home__btn faq-home__btn--mobile">
            <span>Вопросы - ответы</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
            </svg>
        </a>
    </div>
</section>
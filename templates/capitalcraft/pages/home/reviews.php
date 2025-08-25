<?php
require __DIR__ . '/../../data/reviews_data.php';
?>

<section class="reviews frame section-with-divider">
    <div class="container reviews__inner">
        <div class="reviews__title-block">
            <div class="reviews__subtitle">
                <h2>отзывы наших клиентов</h2>
            </div>
        </div>

        <div class="reviews__content">
            <!-- Левая стрелка -->
            <div class="reviews__arrow reviews__arrow--prev">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 101 60" class="reviews__svg">
                    <circle cx="71" cy="30" r="29.5" stroke="#000" fill="transparent" transform="rotate(180 71 30)" class="reviews__circle"/>
                    <path fill="#000" d="M71 31a1 1 0 1 0 0-2v2ZM.293 29.293a1 1 0 0 0 0 1.414l6.364 6.364a1 1 0 0 0 1.414-1.414L2.414 30l5.657-5.657a1 1 0 1 0-1.414-1.414L.293 29.293ZM71 30v-1H1v2h70v-1Z" class="reviews__arrow-path"/>
                </svg>
            </div>

            <!-- Слайдер с отзывами -->
            <div class="reviews__swiper swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($reviews_data as $review): ?>
                        <div class="reviews__slide swiper-slide">
                            <div class="reviews__quote">
                                <div class="reviews__quote-text">
                                    <?= htmlspecialchars($review['text'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div class="reviews__signature">
                                    <div class="reviews__author-name">
                                        <?= htmlspecialchars($review['author'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="reviews__company-name">
                                        <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Пагинация для мобильных -->
                <div class="reviews__pagination swiper-pagination"></div>
            </div>

            <!-- Правая стрелка -->
            <div class="reviews__arrow reviews__arrow--next">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 101 60" class="reviews__svg">
                    <circle cx="30" cy="30" r="29.5" stroke="#000" fill="transparent" transform="matrix(1 0 0 -1 0 60)" class="reviews__circle"/>
                    <path fill="#000" d="M30 31a1 1 0 1 1 0-2v2Zm70.707-1.707a1 1 0 0 1 0 1.414l-6.364 6.364a1 1 0 0 1-1.414-1.414L98.586 30l-5.657-5.657a1 1 0 1 1 1.414-1.414l6.364 6.364ZM30 30v-1h70v2H30v-1Z" class="reviews__arrow-path"/>
                </svg>
            </div>
        </div>
    </div>
</section>
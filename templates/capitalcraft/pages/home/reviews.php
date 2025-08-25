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
                <img src="/templates/capitalcraft/images/home/circle_reviews.svg" alt="" class="reviews__circle" loading="lazy">
                <img src="/templates/capitalcraft/images/home/arrow-l_reviews.svg" alt="Предыдущий отзыв" class="reviews__arrow-icon" loading="lazy">
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
                <img src="/templates/capitalcraft/images/home/circle_reviews.svg" alt="" class="reviews__circle" loading="lazy">
                <img src="/templates/capitalcraft/images/home/arrow-r_reviews.svg" alt="Следующий отзыв" class="reviews__arrow-icon" loading="lazy">
            </div>
        </div>
    </div>
</section>
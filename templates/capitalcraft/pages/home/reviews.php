<?php
require __DIR__ . '/../../data/reviews_data.php';
?>

<section class="reviews frame section-with-divider">
    <div class="container reviews__inner">
        <!-- Упрощенный заголовок без лишнего div -->
        <div class="reviews__subtitle">
            <h2>отзывы наших клиентов</h2>
        </div>

        <!-- Новый контейнер для слайдера и стрелок -->
        <div class="reviews__slider-container">
            <!-- Левая стрелка - вынесена на уровень выше -->
            <button class="reviews__arrow reviews__arrow--prev" type="button" aria-label="Предыдущий отзыв">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 101 60" class="arrow-svg">
                    <circle cx="71" cy="30" r="29.5" stroke="#000" fill="transparent" transform="rotate(180 71 30)" class="arrow-circle"/>
                    <path fill="#000" d="M71 31a1 1 0 1 0 0-2v2ZM.293 29.293a1 1 0 0 0 0 1.414l6.364 6.364a1 1 0 0 0 1.414-1.414L2.414 30l5.657-5.657a1 1 0 1 0-1.414-1.414L.293 29.293ZM71 30v-1H1v2h70v-1Z" class="arrow-path"/>
                </svg>
            </button>

            <!-- Слайдер с отзывами - упрощенная структура -->
            <div class="reviews__swiper swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($reviews_data as $review): ?>
                        <div class="reviews__slide swiper-slide">
                            <!-- Упрощенная структура слайда -->
                            <blockquote class="reviews__quote-text">
                                <?= htmlspecialchars($review['text'], ENT_QUOTES, 'UTF-8') ?>
                            </blockquote>
                            <div class="reviews__signature">
                                <cite class="reviews__author-name">
                                    <?= htmlspecialchars($review['author'], ENT_QUOTES, 'UTF-8') ?>
                                </cite>
                                <div class="reviews__company-name">
                                    <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Пагинация для мобильных - вынесена за пределы слайдера -->
            <div class="reviews__pagination swiper-pagination"></div>

            <!-- Правая стрелка - вынесена на уровень выше -->
            <button class="reviews__arrow reviews__arrow--next" type="button" aria-label="Следующий отзыв">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 101 60" class="arrow-svg">
                    <circle cx="30" cy="30" r="29.5" stroke="#000" fill="transparent" transform="matrix(1 0 0 -1 0 60)" class="arrow-circle"/>
                    <path fill="#000" d="M30 31a1 1 0 1 1 0-2v2Zm70.707-1.707a1 1 0 0 1 0 1.414l-6.364 6.364a1 1 0 1 1-1.414-1.414L98.586 30l-5.657-5.657a1 1 0 1 1 1.414-1.414l6.364 6.364ZM30 30v-1h70v2H30v-1Z" class="arrow-path"/>
                </svg>
            </button>
        </div>
    </div>
</section>

<?php
// Structured data for reviews
$structured_reviews = array_map(function ($review) {
    return [
        '@type' => 'Review',
        'reviewBody' => $review['text'],
        'author' => [
            '@type' => 'Person',
            'name' => $review['author'],
            'affiliation' => [
                '@type' => 'Organization',
                'name' => $review['company'],
            ],
        ],
    ];
}, $reviews_data);

echo "\n<script type=\"application/ld+json\">";
echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Capital Craft',
    'review' => $structured_reviews,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
echo '</script>';
?>
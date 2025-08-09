<?php
$cases = [
    [
        'title' => 'ВТОРОЕ дыхание для транспортной компании',
        'business' => 'Транспортная компания, г. Москва, выручка 210 млн руб',
        'task' => 'Выкупить из лизинга 14 седельных тягачей и рефинансировать обязательства',
        'strategy' => 'Провести аудит -  выявить критичные проблемы, сформировать «единый портрет» бизнеса.  Реализовать контрактное финансирование (243-ФЗ), завершить действующие договоры лизинга, перевести технику на новое юр. лицо (SPV), привлечь банк для отсрочки платежей на 3 месяца',
        'result' => 'Новый график платежей снижен на 27%, владение техникой осуществляется в «чистой» структуре, компания сэкономила 9,4 млн ₽ за первый год, подготовлена основа для участия в гос закупках.'
    ],
    [
        'title' => '30 миллионов для IT компании',
        'business' => 'IT компания, разработка програмного обеспечения',
        'task' => 'Привлечь 30 млн. руб. для доработки продукта и выхода на внешние рынки. Определить роли и формат взаимодействия между инвестором и собственниками.',
        'strategy' => 'За 2 недели создать карту юридической структуры, далее взаимодействовать через ЦФА-платформу (в обмен на квазидолю + защита). Собрать "карту боли" - определить где клиенту не доверяет банк, и где не верит инвестор.',
        'result' => 'Привлечено 26,5 млн руб. за 17 дней, продукт выходит на внешние рынки, компания осталась независимой, уставный капитал не размылся, стратегический инвестор подключён через форму опциона'
    ],
    [
        'title' => '60 миллионов для производства муки',
        'business' => 'Выращивание и переработка зерна в Поволжье',
        'task' => 'Привлечь 60 млн. руб. для запуска мукомольного производства',
        'strategy' => 'Создать SPV с чистыми активами и прогнозным cashflow, подготовить презентацию, offer, финмодель, инвест-мемо и one-pager, привлечь синдикат из 2 частных инвесторов и проектного финансирования.',
        'result' => 'Запустили линии через 45 дней после сделки, инвесторы получили фиксированный долгосрочный доход и участие в бизнесе. Кейс оформлен в виде продукта для повторной упаковки (тиражируемый формат).'
    ]
];
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<section class="show-case frame section-with-divider" id="cases">
    <div class="show-case__inner">
        <div class="show-case__title-block">
            <div class="show-case__subtitle">завершенные кейсы</div>
            <h2 class="show-case__title">Когда стратегия становится результатом</h2>
        </div>

        <div class="show-case__content">
            <div class="show-case__image">
                <img src="/templates/capitalcraft/images/home/handshake.webp" alt="Handshake business partnership" loading="lazy">
            </div>
            <div class="show-case__cases">
                <div class="show-case__swiper swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($cases as $case): ?>
                            <div class="show-case__card swiper-slide">
                                <div class="show-case__card-content">
                                    <div class="show-case__card-title"><?= htmlspecialchars($case['title'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="show-case__details">
                                        <div class="show-case__detail">
                                            <div class="show-case__detail-title">Бизнес клиента</div>
                                            <div class="show-case__detail-description"><?= htmlspecialchars($case['business'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                        <div class="show-case__detail">
                                            <div class="show-case__detail-title">Задача</div>
                                            <div class="show-case__detail-description"><?= htmlspecialchars($case['task'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                        <div class="show-case__detail">
                                            <div class="show-case__detail-title">Стратегия</div>
                                            <div class="show-case__detail-description"><?= htmlspecialchars($case['strategy'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                        <div class="show-case__detail">
                                            <div class="show-case__detail-title">Результат</div>
                                            <div class="show-case__detail-description"><?= htmlspecialchars($case['result'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="show-case__pagination swiper-pagination"></div>
                </div>

                <div class="show-case__button-wrapper">
                    <a href="#" class="btn-main show-case__button">
                        <span>завершенные кейсы</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
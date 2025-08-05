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

<section class="show-case" id="cases">
    <div class="show-case__inner">
        <div class="show-case__title-block">
            <div class="show-case__subtitle">завершенные проекты</div>
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
                        <span>все кейсы</span>
                        <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 7.49969C0 7.12772 0.273914 6.81983 0.628906 6.77118L0.727539 6.76434L13.5117 6.76434L8.89355 2.11884C8.60893 1.83253 8.60823 1.36733 8.8916 1.07977C9.14925 0.818358 9.55271 0.793725 9.83789 1.00653L9.91992 1.07684L15.7861 6.97821C15.8531 7.04562 15.9029 7.12416 15.9385 7.20673C15.9438 7.21911 15.9475 7.23208 15.9521 7.24481C15.9641 7.27737 15.9752 7.30979 15.9824 7.34344C15.9846 7.35343 15.9856 7.36359 15.9873 7.37372C15.9936 7.41069 15.9973 7.44764 15.998 7.48505C15.9981 7.48989 16 7.49482 16 7.49969C16 7.50823 15.9974 7.51662 15.9971 7.52508C15.9961 7.5539 15.9946 7.58247 15.9902 7.61102C15.9874 7.62956 15.9836 7.64765 15.9795 7.66571C15.974 7.68944 15.9678 7.71283 15.96 7.73602C15.9532 7.75611 15.9449 7.77535 15.9365 7.79462C15.9286 7.81277 15.9206 7.83074 15.9111 7.84833C15.8993 7.87037 15.886 7.8912 15.8721 7.9118C15.8666 7.91985 15.8632 7.92932 15.8574 7.93719L15.834 7.96454C15.8248 7.97586 15.8155 7.987 15.8057 7.99774L15.7871 8.0202L9.91992 13.9225C9.63542 14.2088 9.17507 14.2078 8.8916 13.9206C8.63393 13.6592 8.61139 13.2508 8.82324 12.9636L8.89355 12.8815L13.5107 8.23407H0.727539C0.325904 8.23407 6.17158e-05 7.90543 0 7.49969Z" fill="white" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
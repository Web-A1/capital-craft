<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_showcases
 *
 * @copyright   Copyright (C) 2023 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Case data array
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
    ],
    [
        'title' => 'Реструктуризация долга строительной компании',
        'business' => 'Строительная компания, специализация на жилищном строительстве',
        'task' => 'Реструктуризировать долговые обязательства на сумму 150 млн. руб. перед банками и подрядчиками',
        'strategy' => 'Провести финансовый аудит, разработать план реструктуризации, согласовать новые условия с кредиторами, оптимизировать структуру активов компании',
        'result' => 'Долг реструктуризирован с отсрочкой платежей на 18 месяцев, снижение процентной ставки на 3%, компания продолжает операционную деятельность'
    ],
    [
        'title' => 'Привлечение инвестиций для медицинского центра',
        'business' => 'Частный медицинский центр, диагностика и лечение',
        'task' => 'Привлечь 45 млн. руб. для расширения и закупки нового оборудования',
        'strategy' => 'Подготовить инвестиционный меморандум, провести due diligence, структурировать сделку через миноритарный пакет акций',
        'result' => 'Привлечено 45 млн. руб. от частного инвестора, закуплено новое оборудование, увеличена пропускная способность на 40%'
    ]
];

?>

<div class="show-cases-section">
    <div class="show-cases-container">
        <!-- Title Block -->
        <div class="title-block">
            <div class="subtitle-wrapper">
                <div class="subtitle">завершенные проекты</div>
            </div>
            <div class="main-title">
                <h2>Когда стратегия становится результатом</h2>
            </div>
        </div>

        <!-- Content Block -->
        <div class="content-block">
            <!-- Left Image Block -->
            <div class="left-image-block">
                <img src="https://api.builder.io/api/v1/image/assets/TEMP/b445f83d73bfc64c0b409600f96c283a2f76f6e8?width=704" 
                     alt="Handshake business partnership" 
                     class="handshake-image" />
            </div>

            <!-- Right Cases Block -->
            <div class="right-cases-block">
                <!-- Card Stack Container -->
                <div class="card-stack-container" id="cardStack">
                    <!-- Cards will be generated dynamically -->
                    <div class="card card-3" id="card3">
                        <div class="card-content">
                            <div class="card-title"></div>
                        </div>
                    </div>
                    <div class="card card-2" id="card2">
                        <div class="card-content">
                            <div class="card-title"></div>
                        </div>
                    </div>
                    <div class="card card-1 active" id="card1">
                        <div class="card-content">
                            <div class="card-title"></div>
                            <div class="card-details">
                                <div class="detail-block">
                                    <div class="detail-subtitle">Бизнес клиента</div>
                                    <div class="detail-description business-description"></div>
                                </div>
                                <div class="detail-block">
                                    <div class="detail-subtitle">Задача</div>
                                    <div class="detail-description task-description"></div>
                                </div>
                                <div class="detail-block">
                                    <div class="detail-subtitle">Стратегия</div>
                                    <div class="detail-description strategy-description"></div>
                                </div>
                                <div class="detail-block">
                                    <div class="detail-subtitle">Результат</div>
                                    <div class="detail-description result-description"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="action-button-wrapper">
                    <button class="cases-button" type="button">
                        <span class="button-text">все кейсы</span>
                        <svg class="button-arrow" width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 6.99957C0 6.6276 0.273914 6.31971 0.628906 6.27106L0.727539 6.26422L13.5117 6.26422L8.89355 1.61871C8.60893 1.33241 8.60823 0.86721 8.8916 0.579651C9.14925 0.318236 9.55271 0.293603 9.83789 0.506408L9.91992 0.576721L15.7861 6.47809C15.8531 6.5455 15.9029 6.62404 15.9385 6.7066C15.9438 6.71899 15.9475 6.73196 15.9521 6.74469C15.9641 6.77725 15.9752 6.80966 15.9824 6.84332C15.9846 6.85331 15.9856 6.86346 15.9873 6.8736C15.9936 6.91056 15.9973 6.94752 15.998 6.98492C15.9981 6.98977 16 6.9947 16 6.99957C16 7.0081 15.9974 7.0165 15.9971 7.02496C15.9961 7.05378 15.9946 7.08234 15.9902 7.1109C15.9874 7.12944 15.9836 7.14753 15.9795 7.16559C15.974 7.18932 15.9678 7.21271 15.96 7.2359C15.9532 7.25599 15.9449 7.27523 15.9365 7.29449C15.9286 7.31264 15.9206 7.33062 15.9111 7.34821C15.8993 7.37024 15.886 7.39108 15.8721 7.41168C15.8666 7.41973 15.8632 7.4292 15.8574 7.43707L15.834 7.46442C15.8248 7.47574 15.8155 7.48688 15.8057 7.49762L15.7871 7.52008L9.91992 13.4224C9.63542 13.7086 9.17507 13.7077 8.8916 13.4205C8.63393 13.1591 8.61139 12.7506 8.82324 12.4634L8.89355 12.3814L13.5107 7.73395L0.727539 7.73395C0.325904 7.73395 6.17158e-05 7.40531 0 6.99957Z" fill="white"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Load the CSS file
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'modules/mod_showcases/show_cases.css');
?>

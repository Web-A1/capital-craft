<?php defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addStyleSheet('templates/capitalcraft/css/faq.css');

$questions = [
    '«С какими суммами вы работаете?»',
    '«А что, если банк откажет?»',
    '«Вы работаете только с юрлицами или можно как ИП/физлицо?»',
    '«Сколько стоит ваша работа?»',
    '«Сколько времени занимает привлечение финансирования?»',
    '«Работаете ли вы с региональными проектами?»',
    '«Какие документы нужны для старта?»',
    '«Можно ли получить консультацию бесплатно?»',
    '«Сопровождаете ли вы сделки после привлечения средств?»',
];

$faq = [];
foreach ($questions as $i => $question) {
    $faq[] = [
        'q' => $question,
        'a' => 'ответ ' . ($i + 1)
    ];
}
for ($i = 10; $i <= 20; $i++) {
    $faq[] = [
        'q' => "вопрос $i",
        'a' => "ответ $i"
    ];
}
?>

<section class="faq">
    <div class="faq__container">
        <div class="faq__accordion">
            <?php foreach ($faq as $item): ?>
                <div class="faq__item">
                    <h3 class="faq__question"><?= htmlspecialchars($item['q'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="faq__answer"><?= htmlspecialchars($item['a'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <figure class="faq__image">
            <img src="/templates/capitalcraft/images/faq/faq_hand.webp" alt="FAQ">
        </figure>
    </div>
</section>
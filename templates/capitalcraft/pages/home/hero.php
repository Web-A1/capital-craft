<?php
defined("_JEXEC") or die();

require_once JPATH_SITE . "/templates/capitalcraft/helpers/FaqHelper.php";

$faqPageLink = CapitalcraftFaqHelper::getFaqRoute();
?>

<main class="hero frame section-with-divider" role="main">
    <div class="container hero__inner">
        <div class="hero__content">
            <p class="hero__title">1Мы создаем <br> точные решения <br> для вашего капитала</p>
            <h1 class="hero__text">CAPITAL CRAFT — бутиковое агентство инвестиционных решений, специализирующееся на привлечении финансирования для бизнеса. Мы помогаем компаниям найти оптимальные стратегии роста и максимально реализовать их потенциал</h1>
            <a href="<?= htmlspecialchars($faqPageLink, ENT_QUOTES, "UTF-8") ?>" class="btn-main hero__btn">
                <span>вопросы - ответы</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17 17 7m0 0H8m9 0v9" />
                </svg>
            </a>
        </div>
        <div class="hero__image">
            <img src="/templates/capitalcraft/images/home/sphere.svg" alt="Сфера capital craft">
        </div>
    </div>
</main>

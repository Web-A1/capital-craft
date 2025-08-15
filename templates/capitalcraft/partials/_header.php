<header class="site-header frame frame--header section-with-divider">
    <div class="container header__inner">

        <div class="header-logo">
            <a href="/">
                <img src="/templates/capitalcraft/images/logo_red.svg" alt="Логотип Capital Craft (красный)" />
            </a>
        </div>

        <div class="header-right">

            <nav class="main-nav">
                <jdoc:include type="modules" name="header-menu" style="none" />
            </nav>

            <div class="header-controls__inner">
                <div class="divider"></div>

                <button type="button" class="btn btn--primary" data-micromodal-trigger="contact-modal">Обсудить проект</button>

                <div class="divider"></div>
                <div class="header-controls__icons">
                    <a href="https://dzen.ru/capital_craft1" target="_blank" rel="noopener" class="icon-link icon-link--invertable">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
                            <path class="bg" d="M19.92 40.002h.16c7.944 0 12.555-.8 15.82-3.97 3.331-3.364 4.1-8.006 4.1-15.919v-.224c0-7.91-.769-12.521-4.1-15.918C32.638.8 27.994 0 20.083 0h-.16C11.98 0 7.366.8 4.1 3.97.77 7.335 0 11.98 0 19.89v.223c0 7.91 1.769 12.522 4.1 15.919 3.234 3.17 7.879 3.97 15.82 3.97Z" fill="#FDFBF5" stroke="#000" />
                            <path class="icon" d="M35.15 19.936a.29.29 0 0 0-.276-.286c-5.467-.207-8.795-.904-11.123-3.232-2.332-2.333-3.027-5.663-3.234-11.142A.286.286 0 0 0 20.23 5h-.638a.29.29 0 0 0-.285.276c-.207 5.477-.902 8.81-3.235 11.142-2.33 2.33-5.655 3.025-11.123 3.232a.286.286 0 0 0-.276.286v.638a.29.29 0 0 0 .276.285c5.468.207 8.795.905 11.123 3.233 2.328 2.327 3.023 5.648 3.232 11.105.005.153.131.277.286.277h.64a.29.29 0 0 0 .286-.276c.21-5.458.904-8.779 3.232-11.106 2.33-2.33 5.655-3.026 11.123-3.233a.286.286 0 0 0 .276-.285v-.638h.002Z" fill="#000" />
                        </svg>
                    </a>

                    <a href="https://t.me/capital_craft1" target="_blank" rel="noopener" class="icon-link icon-link--invertable">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 40 40">
                            <circle class="bg" cx="20" cy="20" r="19.5" fill="#FDFBF5" stroke="#000" />
                            <path class="icon" d="m27.785 12.099-15.944 6.109c-1.089.434-1.082 1.038-.2 1.306l4.093 1.27 9.472-5.938c.448-.271.857-.126.52.171l-7.673 6.881h-.002l.002.001-.283 4.193c.414 0 .597-.188.829-.411l1.988-1.921 4.136 3.035c.762.418 1.31.203 1.5-.701l2.715-12.714c.278-1.107-.425-1.608-1.153-1.281Z" />
                        </svg>
                    </a>
                </div>
            </div>
            <button class="burger" type="button" aria-label="Открыть меню" aria-controls="mobile-nav" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

        <!-- Мобильное меню теперь внутри header -->
        <div class="mobile-nav">
            <nav class="mobile-nav__menu">
                <jdoc:include type="modules" name="mobile-menu" style="none" />
            </nav>
        </div>
    </div>
</header>
<?php
/**
 * Раздел Инвестиции
 * Использует комплексный компонент bitrix:catalog с шаблоном invest_catalog
 * ДЕТАЛЬНЫЕ СТРАНИЦЫ ОТСУТСТВУЮТ (возврат 404)
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// --- ID инфоблока ---
$IBLOCK_ID = IBLOCK_CONTENT_INVESTICII;

// --- SEO-ТЕГИ ---
$APPLICATION->SetPageProperty("TITLE", "Инвестиции в майнинг от GIS Mining");
$APPLICATION->SetTitle("Инвестиции");
$APPLICATION->SetPageProperty("description", "Инвестиционные решения для майнингового бизнеса от компании GIS Mining.");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- СЛУЖЕБНЫЕ СВОЙСТВА ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title section-title highlighted-color");

// Получаем данные инфоблока
$iblockData = [];
if (CModule::IncludeModule('iblock')) {
    $iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch();
    if ($iblock) {
        $iblockData = [
            'NAME' => $iblock['NAME'],
            'DESCRIPTION' => $iblock['DESCRIPTION'],
        ];
    }
}
?>

<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
    <!-- H1 выводится глобально из header.php -->

    <div class="catalog-page__body">



        <!-- Основной контент -->
        <section class="catalog-page__content section-padding">
            <div class="catalog-content__header">
                <h2 class="catalog-content__title section-title"><?= $APPLICATION->GetTitle() ?></h2>
            </div>

            <?php
            // Комплексный компонент catalog
            $APPLICATION->IncludeComponent(
                "bitrix:catalog",
                "invest_catalog",
                [
                    "IBLOCK_TYPE" => "catalog",
                    "IBLOCK_ID" => $IBLOCK_ID,

                    // SEF режим
                    "SEF_MODE" => "Y",
                    "SEF_FOLDER" => "/catalog/investicii/",
                    "SEF_URL_TEMPLATES" => [
                        "sections" => "",
                        "element" => "#ELEMENT_CODE#/",
                    ],

                    // Настройки сортировки
                    "ELEMENT_SORT_FIELD" => "sort",
                    "ELEMENT_SORT_ORDER" => "asc",
                    "ELEMENT_SORT_FIELD2" => "id",
                    "ELEMENT_SORT_ORDER2" => "desc",

                    // Кэш
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "36000000",
                    "CACHE_GROUPS" => "Y",

                    // Мета-теги
                    "SET_TITLE" => "N",
                    "SET_BROWSER_TITLE" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_LAST_MODIFIED" => "N",
                    "SET_STATUS_404" => "Y",
                    "SHOW_404" => "Y",

                    // Количество элементов
                    "PAGE_ELEMENT_COUNT" => "12",
                    "LINE_ELEMENT_COUNT" => "3",

                    // Свойства
                    "LIST_PROPERTY_CODE" => ["MANUFACTURER", "CRYPTO", "ALGORITHM", "HASHRATE", "EFFICIENCY"],

                    // Пагинация
                    "DISPLAY_TOP_PAGER" => "N",
                    "DISPLAY_BOTTOM_PAGER" => "N",

                    // Прочее
                    "INCLUDE_SUBSECTIONS" => "Y",
                    "SHOW_ALL_WO_SECTION" => "Y",
                    "USE_COMPARE" => "N",
                    "COMPATIBLE_MODE" => "Y",
                ],
                false
            );
            ?>
        </section>
    </div>

    <!-- Описание секции -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: 'Инвестиции' ?></h2>
            <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
            </div>
        </div>
    </section>

    <!-- Секция "Обратная связь" -->
    <? $APPLICATION->IncludeComponent(
        "custom:feedback.section",
        ".default",
        []
    ); ?>
</div>

<!-- PopUp -->
<div class="page-business__popup-form popup-form-wrapper js-cart-modal" id="mainPopupFormWrapper"
    style="display: none;">

    <!-- ВАЖНО: У формы теперь есть класс js-ajax-form, чтобы ее "подхватил" form-actions.js -->
    <form class="popup-form js-ajax-form" id="investCartForm" data-metric-goal="send-invest-lead">

        <button type="button" class="popup-form__close-btn menu-close" id="closeMainPopupFormBtn" aria-label="Закрыть">
            <span>
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 18 18" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 1L1 17M1 1L17 17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                </svg>
            </span>
        </button>

        <p class="popup-form__text">Получить КП на инвестиции</p>

        <p class="popup-form__cta">Заполните форму, чтобы оставить заявку на КП. Мы перезвоним вам в
            ближайшее время</p>

        <label for="cart_client_name">Ваше имя*:</label>
        <input type="text" name="client_name" id="cart_client_name" placeholder="Имя"
            class="contact-form__input form-input" required aria-label="Имя">

        <label for="cart_client_phone">Телефон*:</label>
        <input type="tel" name="client_phone" id="cart_client_phone" placeholder="Телефон*"
            class="contact-form__input form-input js-phone-mask" required aria-label="Номер телефона">

        <label for="cart_client_email">Email:</label>
        <input type="email" name="client_email" id="invest_cart_client_email"
            placeholder="your@email.com (необязательно)" class="contact-form__input form-input"
            aria-label="Электронная почта">

        <input type="hidden" name="source_id" value="43">
        <input type="hidden" name="utm_source">
        <input type="hidden" name="utm_medium">
        <input type="hidden" name="utm_campaign">
        <input type="hidden" name="utm_content">
        <input type="hidden" name="utm_term">
        <!-- Название формы для CRM -->
        <input type="hidden" name="form_name" value="Заказ из каталога инвестиций">
        <input type="hidden" name="page_url" value="">

        <button type="submit" class="btn btn-primary">Получить КП</button>

        <div class="form-group form-check mb-3">
            <input type="checkbox" id="privacy-policy-main" name="privacy-policy" class="form-check-input" required>
            <label for="privacy-policy-main" class="form-check-label">Согласен(а) с <a href="/policy-confidenciales/"
                    target="_blank"><u>политикой
                        конфиденциальности</u></a></label>
        </div>
        <p class="form-error-message" style="color: red; display: none;"></p>
    </form>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
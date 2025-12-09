<?php
/**
 * Раздел Инвестиции
 * Использует комплексный компонент bitrix:catalog с шаблоном invest_catalog
 * ДЕТАЛЬНЫЕ СТРАНИЦЫ ОТСУТСТВУЮТ (возврат 404)
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// --- ID инфоблока ---
$IBLOCK_ID = IBLOCK_CONTENT_INVESTICII;

// --- ПОДГОТОВКА ДАННЫХ ---
$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];
$pageUrl = $APPLICATION->GetCurPage(false);
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';

// --- SEO-ТЕГИ ---
$APPLICATION->SetPageProperty("TITLE", "Инвестиции в майнинг от GIS Mining");
$APPLICATION->SetTitle("Инвестиции");
$APPLICATION->SetPageProperty("description", "Инвестиционные решения для майнингового бизнеса от компании GIS Mining.");
$APPLICATION->SetPageProperty("robots", "index, follow");

// --- OPEN GRAPH ---
$APPLICATION->SetPageProperty("OG:TITLE", "Инвестиции в майнинг от GIS Mining");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Инвестиционные решения для майнингового бизнеса от компании GIS Mining.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD ---
$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Инвестиции в майнинг от GIS Mining");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Инвестиционные решения для майнингового бизнеса от компании GIS Mining.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА ---
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title highlighted-color");

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
        <!-- Сайдбар БЕЗ фильтра -->
        <?php
        $APPLICATION->IncludeComponent(
            "custom:catalog.sidebar",
            ".default",
            [
                "IBLOCK_ID" => $IBLOCK_ID,
                "SHOW_FILTER" => "N"
            ]
        );
        ?>

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

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
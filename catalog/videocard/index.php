<?php
/**
 * Раздел Видеокарты
 * 
 * Использует комплексный компонент bitrix:catalog с шаблоном tech_catalog.
 * Вся логика разметки находится в шаблонах компонента:
 * - section.php — список товаров (с сайдбаром, поиском, описанием)
 * - element.php — детальная страница товара
 * - smart_filter.php — отфильтрованный список
 * 
 * @canonical Bitrix approach — минимальный index.php
 */

use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// --- Подключение модулей ---
if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    echo "Не удалось подключить модули iblock или catalog";
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

// --- ID инфоблока ---
$IBLOCK_ID = IBLOCK_CATALOG_VIDEOCARD;
$sefFolder = '/catalog/videocard/';

// ====================================================================
// SEO-НАСТРОЙКИ ДЛЯ СТРАНИЦЫ РАЗДЕЛА
// (Для детальной страницы SEO управляется в element.php)
// ====================================================================
$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];
$pageUrl = $APPLICATION->GetCurPage(false);
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';

$APPLICATION->SetPageProperty("TITLE", "Купить видеокарту для майнинга — каталог GIS Mining 2025");
$APPLICATION->SetTitle("Видеокарты");
$APPLICATION->SetPageProperty("description", "Видеокарты для майнинга криптовалют. Официальные поставки с гарантией. GIS Mining - ваш надежный поставщик.");
$APPLICATION->SetPageProperty("robots", "index, follow");

// Open Graph
$APPLICATION->SetPageProperty("OG:TITLE", "Купить видеокарту для майнинга — каталог GIS Mining 2025");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Видеокарты для майнинга криптовалют.");
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// Twitter Card
$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Купить видеокарту для майнинга — каталог GIS Mining 2025");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Видеокарты для майнинга криптовалют.");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// Свойства для шаблона
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ====================================================================
// SEF URL ШАБЛОНЫ
// ВАЖНО: Порядок критичен! Более специфичные должны идти ПЕРВЫМИ!
// ====================================================================
$sefUrlTemplates = [
    "smart_filter" => "filter/#SMART_FILTER_PATH#/apply/", // ПЕРВЫМ!
    "calculator" => "#ELEMENT_CODE#/calculator-dohodnosti/",
    "sections" => "",
    "section" => "#SECTION_CODE_PATH#/",
    "element" => "#ELEMENT_CODE#/",
];

// ====================================================================
// КОМПЛЕКСНЫЙ КОМПОНЕНТ КАТАЛОГА
// Вся разметка — внутри шаблонов (section.php, element.php)
// ====================================================================
$APPLICATION->IncludeComponent(
    "bitrix:catalog",
    "tech_catalog",
    [
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => $IBLOCK_ID,
        "BASKET_URL" => "/basket/",
        "ACTION_VARIABLE" => "action",
        "PRODUCT_ID_VARIABLE" => "id",
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "PRODUCT_QUANTITY_VARIABLE" => "quantity",
        "PRODUCT_PROPS_VARIABLE" => "prop",

        // SEF режим
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => $sefFolder,
        "SEF_URL_TEMPLATES" => $sefUrlTemplates,
        "DETAIL_STRICT_SECTION_CHECK" => "N",

        // Настройки отображения
        "USE_COMPARE" => "N",
        "USE_FILTER" => "Y",
        "USE_REVIEW" => "N",
        "USE_ELEMENT_COUNTER" => "Y",

        // Цены
        "PRICE_CODE" => ["BASE"],
        "USE_PRICE_COUNT" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "PRICE_VAT_INCLUDE" => "Y",

        // Кэш
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "CACHE_GROUPS" => "Y",
        "CACHE_FILTER" => "N",

        // Мета-теги
        "SET_TITLE" => "N",  // Управляется в index.php
        "SET_BROWSER_TITLE" => "N",
        "SET_META_KEYWORDS" => "N",
        "SET_META_DESCRIPTION" => "N",
        "SET_LAST_MODIFIED" => "N",
        // 404 отключен - иначе компонент не вызывает шаблон для URL фильтра
        "SET_STATUS_404" => "N",
        "SHOW_404" => "N",
        "FILE_404" => "",

        // Количество элементов
        "PAGE_ELEMENT_COUNT" => "12",
        "LINE_ELEMENT_COUNT" => "3",

        // Свойства
        "LIST_PROPERTY_CODE" => [
            "MANUFACTURER",
            "CRYPTO",
            "ALGORITHM",
            "HASHRATE",
            "HASHRATE_TH",
            "HASHRATE_MH",
            "HASHRATE_KSOL",
            "CONSUMPTION",
            "EFFICIENCY",
        ],
        "DETAIL_PROPERTY_CODE" => [
            "MANUFACTURER",
            "CRYPTO",
            "ALGORITHM",
            "HASHRATE",
            "HASHRATE_TH",
            "HASHRATE_MH",
            "HASHRATE_KSOL",
            "CONSUMPTION",
            "EFFICIENCY",
            "NOISE_LEVEL",
            "DIMENSIONS",
            "WEIGHT",
            "TEMPERATURE",
            "INTERFACE",
            "MORE_PHOTO",
            "WARRANTY",
            "FEATURED",
        ],

        // Пагинация
        "PAGER_TEMPLATE" => ".default",
        "PAGER_TITLE" => "Товары",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "Y",

        // Прочее
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "INCLUDE_SUBSECTIONS" => "Y",
        "SHOW_ALL_WO_SECTION" => "Y",
        "TEMPLATE_THEME" => "blue",
        "COMPATIBLE_MODE" => "Y",
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

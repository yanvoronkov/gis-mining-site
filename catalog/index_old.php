<?php
/**
 * Главная страница каталога - комплексный компонент
 * Это ТЕСТОВАЯ версия, основная находится в index.php
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Каталог оборудования для майнинга");

$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title highlighted-color");

// Определяем IBLOCK_ID по коду раздела из URL
$sectionCode = $_REQUEST['SECTION_CODE'] ?? '';

// Мапинг кодов разделов на ID инфоблоков
$sectionToIblock = [
    "asics" => 1,
    "videocard" => 11,
    "gpu" => 5,
    "investicii" => 4,
    "conteynery" => 3,
    "gotovyy-biznes" => 6,
];

$iblockId = $sectionToIblock[$sectionCode] ?? 1; // По умолчанию ASICS

// ИСПРАВЛЕНИЕ МАРШРУТИЗАЦИИ:
// Если компонент ошибся и определил страницу как 'section', но в URL есть код элемента или фильтра,
// мы принудительно меняем страницу.
if (isset($_REQUEST["ELEMENT_CODE"]) && !empty($_REQUEST["ELEMENT_CODE"])) {
    // ... (логика элемента остается)
} elseif (isset($_REQUEST["SMART_FILTER_PATH"]) && !empty($_REQUEST["SMART_FILTER_PATH"])) {
    // Очищаем путь фильтра от GET-параметров, если они туда попали
    $cleanFilterPath = explode('?', $_REQUEST["SMART_FILTER_PATH"])[0];
    $_REQUEST["SMART_FILTER_PATH"] = $cleanFilterPath;

    // ... (остальная логика)
}

// Подключаем комплексный компонент каталога
$APPLICATION->IncludeComponent(
    "bitrix:catalog",
    ".default",
    [
        // Основные параметры
        "IBLOCK_TYPE" => "catalog",
        "IBLOCK_ID" => $iblockId, // Определяется по коду раздела из URL

        // SEF режим
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/catalog/",
        "SEF_URL_TEMPLATES" => [
            "sections" => "",
            "smart_filter" => "#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/",
            "element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
            // Калькулятор доходности
            "dohodnost" => "#SECTION_CODE#/#ELEMENT_CODE#/calculator-dohodnosti/",
            "section" => "#SECTION_CODE#/",
            "compare" => "compare/",
        ],

        // Переменные
        "VARIABLE_ALIASES" => [
            "SECTION_CODE" => "SECTION_CODE",
            "ELEMENT_CODE" => "ELEMENT_CODE",
            "SMART_FILTER_PATH" => "SMART_FILTER_PATH",
        ],

        // Использовать код вместо ID
        "USE_ELEMENT_COUNTER" => "Y",

        // Шаблоны подкомпонентов (будут переопределены динамически)
        "SECTIONS_LIST_TEMPLATE" => ".default",
        "SECTION_TEMPLATE" => ".default",
        "ELEMENT_TEMPLATE" => ".default",
        "FILTER_TEMPLATE" => "smart_filter",

        // Кеширование
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "CACHE_GROUPS" => "Y",
        "CACHE_FILTER" => "Y",

        // Общие настройки
        "USE_COMPARE" => "N",
        "PRICE_CODE" => ["BASE"],
        "USE_PRICE_COUNT" => "N",
        "SHOW_PRICE_COUNT" => "1",
        "PRICE_VAT_INCLUDE" => "Y",
        "CONVERT_CURRENCY" => "N",
        "BASKET_URL" => "/basket/",

        // SEO
        "ADD_SECTIONS_CHAIN" => "Y",
        "ADD_ELEMENT_CHAIN" => "Y",
        "SET_LAST_MODIFIED" => "N",

        // Настройки раздела
        "SECTION_ID_VARIABLE" => "SECTION_ID",
        "SECTION_CODE_VARIABLE" => "SECTION_CODE",
        "SECTION_URL" => "",
        "DETAIL_URL" => "",

        // Настройки элемента
        "ELEMENT_ID_VARIABLE" => "ELEMENT_ID",
        "ELEMENT_CODE_VARIABLE" => "ELEMENT_CODE",
        "USE_MAIN_ELEMENT_SECTION" => "N",
        "ELEMENT_SORT_FIELD" => "sort",
        "ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_FIELD2" => "id",
        "ELEMENT_SORT_ORDER2" => "desc",

        // Фильтр
        "FILTER_NAME" => "arrFilter",
        "FILTER_FIELD_CODE" => [],
        "FILTER_PROPERTY_CODE" => [],
        "FILTER_PRICE_CODE" => ["BASE"],
        "FILTER_OFFERS_FIELD_CODE" => [],
        "FILTER_OFFERS_PROPERTY_CODE" => [],

        // Пагинация
        "DISPLAY_TOP_PAGER" => "N",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "PAGER_TITLE" => "Товары",
        "PAGER_SHOW_ALWAYS" => "N",
        "PAGER_TEMPLATE" => "catalog_new",
        "PAGER_DESC_NUMBERING" => "N",
        "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
        "PAGER_SHOW_ALL" => "N",
        "PAGER_BASE_LINK_ENABLE" => "N",

        // Настройки отображения товаров
        "PRODUCT_ID_VARIABLE" => "id",
        "ACTION_VARIABLE" => "action",
        "PRODUCT_ROW_VARIANTS" => "[{'VARIANT':'3','BIG_DATA':false}]",
        "PRODUCT_BLOCKS_ORDER" => "price,props,sku,quantityLimit,quantity,buttons",
        "SHOW_SKU_DESCRIPTION" => "N",
        "PRODUCT_DISPLAY_MODE" => "Y",

        // Количество товаров
        "LINE_ELEMENT_COUNT" => "3",
        "ELEMENT_COUNT_NEW" => "12",
        "ELEMENT_COUNT_HIT" => "12",
        "PAGE_ELEMENT_COUNT" => "12",

        // Свойства для вывода
        "PROPERTY_CODE" => [
            "MANUFACTURER",
            "CRYPTO",
            "ALGORITHM",
            "HASHRATE",
            "HASHRATE_TH",
            "EFFICIENCY",
            "POWER",
            "FEATURED",
            "IN_STOCK",
            "AVAILABILITY",
        ],

        // Настройки корзины
        "USE_PRODUCT_QUANTITY" => "N",
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "PRODUCT_PROPERTIES" => [],
        "ADD_TO_BASKET_ACTION" => "ADD",
        "SHOW_CLOSE_POPUP" => "N",

        // Дополнительные настройки
        "HIDE_NOT_AVAILABLE" => "N",
        "HIDE_NOT_AVAILABLE_OFFERS" => "N",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "SET_TITLE" => "Y",
        "SET_BROWSER_TITLE" => "Y",
        "SET_META_KEYWORDS" => "Y",
        "SET_META_DESCRIPTION" => "Y",
        "SET_STATUS_404" => "N",
        "SHOW_404" => "N",
        "MESSAGE_404" => "",
        "FILE_404" => "",

        // AJAX
        "AJAX_MODE" => "N",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N",

        // Совместимость
        "COMPATIBLE_MODE" => "N",
        "COMPOSITE_FRAME_MODE" => "A",
        "COMPOSITE_FRAME_TYPE" => "AUTO",
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

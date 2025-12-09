<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - tech_catalog
 * Контроллер для страницы списка раздела (section.php)
 * 
 * Используется для ASIC и Videocard разделов.
 * Содержит полную разметку страницы: сайдбар, поиск, список товаров.
 * 
 * @canonical Bitrix approach - вся разметка внутри шаблона
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

Loader::includeModule('iblock');
Loader::includeModule('catalog');

// ====================================================================
// ОПРЕДЕЛЕНИЕ ТЕКУЩЕГО РАЗДЕЛА КАТАЛОГА
// ====================================================================
$IBLOCK_ID = $arParams["IBLOCK_ID"];
$sefFolder = $arResult["FOLDER"];

// Определяем SEF-правило для фильтра
$filterSefRule = $sefFolder . "filter/#SMART_FILTER_PATH#/apply/";

// ====================================================================
// ИЗВЛЕЧЕНИЕ SMART_FILTER_PATH ДЛЯ ФИЛЬТРА
// ====================================================================
// Bitrix иногда не распознаёт URL фильтра и передаёт его как SECTION_CODE_PATH.
// Проверяем, если URL содержит /filter/.../apply/ — это фильтр!
$smartFilterPath = $arResult["VARIABLES"]["SMART_FILTER_PATH"] ?? '';

// Если SMART_FILTER_PATH пустой, но URL выглядит как фильтр — извлекаем вручную
if (empty($smartFilterPath)) {
    $requestUri = $_SERVER["REQUEST_URI"];
    if (preg_match('#/filter/([^?]+)/apply/?#', $requestUri, $matches)) {
        $smartFilterPath = rtrim($matches[1], '/');
        $arResult["VARIABLES"]["SMART_FILTER_PATH"] = $smartFilterPath;
        // Очищаем SECTION_CODE_PATH, т.к. это не раздел
        $arResult["VARIABLES"]["SECTION_CODE_PATH"] = '';
    }
}

// Устанавливаем в $_REQUEST для сайдбара
if (!empty($smartFilterPath)) {
    $_REQUEST["SMART_FILTER_PATH"] = $smartFilterPath;
}

// ====================================================================
// ГЛОБАЛЬНЫЙ ФИЛЬТР ДЛЯ КОМПОНЕНТОВ
// ====================================================================
// Кастомная сортировка реализована в result_modifier.php шаблона catalog.section
// Это позволяет сохранить работу пагинации
// ====================================================================
global $arrFilter;
if (!is_array($arrFilter)) {
    $arrFilter = [];
}

// ====================================================================
// ДАННЫЕ ИНФОБЛОКА ДЛЯ ОПИСАНИЯ
// ====================================================================
$iblockData = [];
if ($iblock = CIBlock::GetByID($IBLOCK_ID)->Fetch()) {
    $iblockData = [
        'NAME' => $iblock['NAME'],
        'DESCRIPTION' => $iblock['DESCRIPTION'],
    ];
}

// Определяем заголовок раздела
$sectionTitle = "Каталог";
if ($IBLOCK_ID == IBLOCK_CATALOG_ASICS) {
    $sectionTitle = "Каталог ASIC майнеров для добычи криптовалют";
} elseif ($IBLOCK_ID == IBLOCK_CATALOG_VIDEOCARD) {
    $sectionTitle = "Каталог видеокарт для майнинга криптовалют";
}

?>

<!-- ====================================================================
     РАЗМЕТКА СТРАНИЦЫ СПИСКА ТОВАРОВ
     ==================================================================== -->
<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $IBLOCK_ID ?>">
    <!-- H1 выводится глобально из header.php -->

    <!-- Компонент живого поиска (мобильный) -->
    <div class="catalog-search-mobile">
        <?php $APPLICATION->IncludeComponent(
            "custom:catalog.search",
            ".default",
            [
                "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
                "MIN_QUERY_LENGTH" => 2,
                "MAX_RESULTS" => 10,
                "SHOW_PRICE" => "Y",
                "PRICE_CODE" => "BASE",
                "CACHE_TIME" => 3600,
            ]
        ); ?>
    </div>

    <div class="catalog-page__body">
        <!-- Сайдбар -->
        <?php $APPLICATION->IncludeComponent(
            "custom:catalog.sidebar",
            ".default",
            [
                "IBLOCK_ID" => $IBLOCK_ID,
                "SHOW_FILTER" => "Y",
                "SEF_RULE" => $filterSefRule,
                "FILTER_NAME" => "arrFilter",
            ]
        ); ?>

        <!-- Основной контент -->
        <section class="catalog-page__content section-padding">
            <div class="catalog-content__header">
                <h2 class="catalog-content__title section-title"><?= $iblockData['NAME'] ?: $APPLICATION->GetTitle() ?>
                </h2>
                <!-- Компонент живого поиска (десктоп) -->
                <div class="catalog-search">
                    <?php $APPLICATION->IncludeComponent(
                        "custom:catalog.search",
                        ".default",
                        [
                            "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
                            "MIN_QUERY_LENGTH" => 2,
                            "MAX_RESULTS" => 10,
                            "SHOW_PRICE" => "Y",
                            "PRICE_CODE" => "BASE",
                            "CACHE_TIME" => 3600,
                        ]
                    ); ?>
                </div>
            </div>

            <?php
            // ====================================================================
            // КОМПОНЕНТ СПИСКА ТОВАРОВ
            // ====================================================================
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section",
                ".default",
                [
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "ELEMENT_SORT_FIELD" => "PROPERTY_SORT_PRIORITY",
                    "ELEMENT_SORT_ORDER" => "DESC",
                    "ELEMENT_SORT_FIELD2" => "CATALOG_PRICE_1",
                    "ELEMENT_SORT_ORDER2" => "ASC",
                    "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
                    "META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
                    "META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
                    "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                    "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
                    "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                    "BASKET_URL" => $arParams["BASKET_URL"],
                    "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                    "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                    "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                    "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                    "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                    "FILTER_NAME" => "arrFilter",
                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                    "SET_TITLE" => $arParams["SET_TITLE"],
                    "SET_BROWSER_TITLE" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                    "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                    "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
                    "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                    "PRICE_CODE" => $arParams["PRICE_CODE"],
                    "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                    "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                    "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                    "USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
                    "ADD_PROPERTIES_TO_BASKET" => "Y",
                    "PRODUCT_PROPERTIES" => [],
                    "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                    "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                    "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                    "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                    "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
                    "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                    "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
                    "PAGER_BASE_LINK_ENABLE" => $arParams["PAGER_BASE_LINK_ENABLE"],
                    "PAGER_BASE_LINK" => $arParams["PAGER_BASE_LINK"],
                    "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                    "LAZY_LOAD" => $arParams["LAZY_LOAD"],
                    "MESS_BTN_LAZY_LOAD" => $arParams["MESS_BTN_LAZY_LOAD"],
                    "LOAD_ON_SCROLL" => $arParams["LOAD_ON_SCROLL"],
                    "SHOW_ALL_WO_SECTION" => "Y",
                    "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                    "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                    "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                    "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                    "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
                    "CURRENCY_ID" => $arParams["CURRENCY_ID"],
                    "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
                    "HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
                    "LABEL_PROP" => $arParams["LABEL_PROP"],
                    "LABEL_PROP_MOBILE" => $arParams["LABEL_PROP_MOBILE"],
                    "LABEL_PROP_POSITION" => $arParams["LABEL_PROP_POSITION"],
                    "ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
                    "PRODUCT_DISPLAY_MODE" => $arParams["PRODUCT_DISPLAY_MODE"],
                    "PRODUCT_BLOCKS_ORDER" => $arParams["LIST_PRODUCT_BLOCKS_ORDER"],
                    "PRODUCT_ROW_VARIANTS" => $arParams["LIST_PRODUCT_ROW_VARIANTS"],
                    "ENLARGE_PRODUCT" => $arParams["LIST_ENLARGE_PRODUCT"],
                    "ENLARGE_PROP" => $arParams["LIST_ENLARGE_PROP"],
                    "SHOW_SLIDER" => $arParams["LIST_SHOW_SLIDER"],
                    "SLIDER_INTERVAL" => $arParams["LIST_SLIDER_INTERVAL"],
                    "SLIDER_PROGRESS" => $arParams["LIST_SLIDER_PROGRESS"],
                    "OFFER_ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
                    "OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
                    "PRODUCT_SUBSCRIPTION" => $arParams["PRODUCT_SUBSCRIPTION"],
                    "SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
                    "DISCOUNT_PERCENT_POSITION" => $arParams["DISCOUNT_PERCENT_POSITION"],
                    "SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
                    "SHOW_MAX_QUANTITY" => $arParams["SHOW_MAX_QUANTITY"],
                    "MESS_SHOW_MAX_QUANTITY" => $arParams["~MESS_SHOW_MAX_QUANTITY"],
                    "RELATIVE_QUANTITY_FACTOR" => $arParams["RELATIVE_QUANTITY_FACTOR"],
                    "MESS_RELATIVE_QUANTITY_MANY" => $arParams["~MESS_RELATIVE_QUANTITY_MANY"],
                    "MESS_RELATIVE_QUANTITY_FEW" => $arParams["~MESS_RELATIVE_QUANTITY_FEW"],
                    "MESS_BTN_BUY" => $arParams["~MESS_BTN_BUY"],
                    "MESS_BTN_ADD_TO_BASKET" => $arParams["~MESS_BTN_ADD_TO_BASKET"],
                    "MESS_BTN_SUBSCRIBE" => $arParams["~MESS_BTN_SUBSCRIBE"],
                    "MESS_BTN_DETAIL" => $arParams["~MESS_BTN_DETAIL"],
                    "MESS_NOT_AVAILABLE" => $arParams["~MESS_NOT_AVAILABLE"],
                    "MESS_BTN_COMPARE" => $arParams["~MESS_BTN_COMPARE"],
                    "USE_ENHANCED_ECOMMERCE" => $arParams["USE_ENHANCED_ECOMMERCE"],
                    "DATA_LAYER_NAME" => $arParams["DATA_LAYER_NAME"],
                    "BRAND_PROPERTY" => $arParams["BRAND_PROPERTY"],
                    "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                    "ADD_SECTIONS_CHAIN" => "N",
                    "ADD_TO_BASKET_ACTION" => $arParams["ADD_TO_BASKET_ACTION"],
                    "SHOW_CLOSE_POPUP" => $arParams["SHOW_CLOSE_POPUP"],
                    "COMPARE_PATH" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["compare"],
                    "COMPARE_NAME" => $arParams["COMPARE_NAME"],
                    "USE_COMPARE_LIST" => "Y",
                    "BACKGROUND_IMAGE" => $arParams["SECTION_BACKGROUND_IMAGE"],
                    "COMPATIBLE_MODE" => "Y",
                    "DISABLE_INIT_JS_IN_COMPONENT" => "N",
                ],
                $component,
                ["HIDE_ICONS" => "Y"]
            );
            ?>
        </section>
    </div>

    <!-- Описание раздела -->
    <section class="catalog-about section-padding">
        <div class="about__content">
            <h2 class="about__title"><?= $iblockData['NAME'] ?: $sectionTitle ?></h2>
            <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                <?= $iblockData['DESCRIPTION'] ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
            </div>
        </div>
    </section>

    <!-- Секция "Обратная связь" -->
    <?php $APPLICATION->IncludeComponent("custom:feedback.section", ".default", []); ?>
</div>
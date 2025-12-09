<?php
/**
 * Шаблон комплексного компонента bitrix:catalog - tech_catalog
 * Специальная страница калькулятора доходности (calculator.php)
 * 
 * Используется только для ASIC раздела
 * Вызывается из element.php при установленном флаге SHOW_CALCULATOR=Y
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;

// Подключаем необходимые модули
Loader::includeModule('iblock');
Loader::includeModule('catalog');

// ====================================================================
// НАСТРОЙКА СТРАНИЦЫ ДЛЯ КАЛЬКУЛЯТОРА ДОХОДНОСТИ
// ====================================================================
// Аналогично детальной странице товара — применяем стили product-page
$APPLICATION->SetPageProperty("main_class", "product-page");
// Скрываем стандартный заголовок H1 (будет свой в шаблоне)
$APPLICATION->SetPageProperty("HIDE_TITLE", "Y");

// ====================================================================
// СТРАНИЦА КАЛЬКУЛЯТОРА ДОХОДНОСТИ
// ====================================================================

// Вызов компонента детального просмотра с шаблоном "dohodnost"
$APPLICATION->IncludeComponent(
    "bitrix:catalog.element",
    "dohodnost",
    [
        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "PROPERTY_CODE" => $arParams["DETAIL_PROPERTY_CODE"],
        "META_KEYWORDS" => $arParams["DETAIL_META_KEYWORDS"],
        "META_DESCRIPTION" => $arParams["DETAIL_META_DESCRIPTION"],
        "BROWSER_TITLE" => $arParams["DETAIL_BROWSER_TITLE"],
        "SET_CANONICAL_URL" => $arParams["DETAIL_SET_CANONICAL_URL"],
        "BASKET_URL" => $arParams["BASKET_URL"],
        "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
        "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
        "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
        "CHECK_SECTION_ID_VARIABLE" => "N",
        "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
        "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
        "CACHE_TIME" => $arParams["CACHE_TIME"],
        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
        "SET_TITLE" => "N",  // Отключено — заголовок и хлебные крошки формируем вручную
        "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
        "MESSAGE_404" => $arParams["~MESSAGE_404"],
        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
        "SHOW_404" => $arParams["SHOW_404"],
        "FILE_404" => $arParams["FILE_404"],
        "PRICE_CODE" => $arParams["PRICE_CODE"],
        "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
        "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
        "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
        "PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"],
        "USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
        "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
        "ADD_PROPERTIES_TO_BASKET" => "Y",
        "PARTIAL_PRODUCT_PROPERTIES" => "N",
        "LINK_IBLOCK_TYPE" => $arParams["LINK_IBLOCK_TYPE"],
        "LINK_IBLOCK_ID" => $arParams["LINK_IBLOCK_ID"],
        "LINK_PROPERTY_SID" => $arParams["LINK_PROPERTY_SID"],
        "LINK_ELEMENTS_URL" => $arParams["LINK_ELEMENTS_URL"],
        "ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
        "ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
        "SECTION_ID" => "",
        "SECTION_CODE" => "",
        "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
        "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
        "CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
        "CURRENCY_ID" => $arParams["CURRENCY_ID"],
        "HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
        "USE_ELEMENT_COUNTER" => $arParams["USE_ELEMENT_COUNTER"],
        "SHOW_DEACTIVATED" => $arParams["SHOW_DEACTIVATED"],
        "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
        "STRICT_SECTION_CHECK" => $arParams["DETAIL_STRICT_SECTION_CHECK"],
        "ADD_SECTIONS_CHAIN" => "N",  // Отключено — добавляем вручную в component_epilog.php
        "ADD_ELEMENT_CHAIN" => "N",  // Отключено — добавляем вручную в component_epilog.php
        "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
        "COMPATIBLE_MODE" => "Y",
        "DISABLE_INIT_JS_IN_COMPONENT" => "N",
        "SET_VIEWED_IN_COMPONENT" => "Y",
    ],
    $component,
    ["HIDE_ICONS" => "Y"]
);
?>
<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */

// Минимальный результат-модификатор: определяем код раздела и подставляем ID инфоблока
$sectionCode = $arResult["VARIABLES"]["SECTION_CODE"] ?? $arResult["VARIABLES"]["SECTION_CODE_PATH"] ?? "";

if ($sectionCode === "" && isset($_SERVER['REQUEST_URI'])) {
    $uri = explode('?', $_SERVER['REQUEST_URI'])[0];
    $segments = array_filter(explode('/', trim($uri, '/')));
    if (count($segments) >= 2 && $segments[0] === 'catalog') {
        $sectionCode = $segments[1];
    }
}

// Маппинг кодов разделов на ID инфоблоков (через константы)
$sectionToIblock = [
    "asics" => IBLOCK_CATALOG_ASICS,
    "videocard" => IBLOCK_CATALOG_VIDEOCARD,
    "gpu" => IBLOCK_CONTENT_GPU,
    "investicii" => IBLOCK_CONTENT_INVESTICII,
    "conteynery" => IBLOCK_CONTENT_CONTAINERS,
    "gotovyy-biznes" => IBLOCK_CONTENT_BUSINESS,
];

// Если в параметры не передали IBLOCK_ID, определяем по коду раздела
if (empty($arParams["IBLOCK_ID"]) && isset($sectionToIblock[$sectionCode])) {
    $arParams["IBLOCK_ID"] = $sectionToIblock[$sectionCode];
}

// Подставляем шаблоны, если не заданы явно
$sectionTemplates = [
    IBLOCK_CATALOG_ASICS => ".default",
    IBLOCK_CATALOG_VIDEOCARD => ".default",
    IBLOCK_CONTENT_GPU => "gpu_section",
    IBLOCK_CONTENT_INVESTICII => "investments_section",
    IBLOCK_CONTENT_CONTAINERS => "containers_section",
    IBLOCK_CONTENT_BUSINESS => "business_section",
];

$elementTemplates = [
    IBLOCK_CATALOG_ASICS => "catalog_element",
    IBLOCK_CATALOG_VIDEOCARD => "catalog_element",
    IBLOCK_CONTENT_GPU => "catalog_element",
    IBLOCK_CONTENT_INVESTICII => "catalog_element",
    IBLOCK_CONTENT_BUSINESS => "catalog_element",
    IBLOCK_CONTENT_CONTAINERS => "catalog_element",
];

$iblockId = (int) ($arParams["IBLOCK_ID"] ?? 0);
$arParams["SECTION_TEMPLATE"] = $arParams["SECTION_TEMPLATE"] ?? ($sectionTemplates[$iblockId] ?? ".default");
$arParams["ELEMENT_TEMPLATE"] = $arParams["ELEMENT_TEMPLATE"] ?? ($elementTemplates[$iblockId] ?? "catalog_element");
$arParams["SECTION_CODE"] = $sectionCode;

$arResult["CURRENT_IBLOCK_ID"] = $iblockId;
$arResult["CURRENT_SECTION_CODE"] = $sectionCode;

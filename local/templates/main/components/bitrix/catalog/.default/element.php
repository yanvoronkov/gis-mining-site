<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */
/** @global CMain $APPLICATION */

// Этот файл является обёрткой для catalog.element компонента
// Передаем управление соответствующему шаблону catalog.element

$this->setFrameMode(true);

// Получаем ID инфоблока и код элемента
$iblockId = $arParams["IBLOCK_ID"];
$elementTemplate = $arParams["ELEMENT_TEMPLATE"] ?? "catalog_element";

?>

<div class="catalog-page catalog-new product-page container" id="app-root">
    <?php
    // Добавляем переменные из SEF в параметры
    if (isset($arResult["VARIABLES"]["ELEMENT_CODE"])) {
        $arParams["ELEMENT_CODE"] = $arResult["VARIABLES"]["ELEMENT_CODE"];
    }
    if (isset($arResult["VARIABLES"]["SECTION_CODE"])) {
        $arParams["SECTION_CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];
    }

    // ОТЛАДКА: Проверяем существование элемента напрямую
    // Removed debug output
    
    // Подключаем компонент catalog.element с нужным шаблоном
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.element",
        $elementTemplate,
        $arParams,
        $component,
        ["HIDE_ICONS" => "Y"]
    );
    ?>
</div>
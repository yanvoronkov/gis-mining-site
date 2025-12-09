<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */
/** @global CMain $APPLICATION */

// Логика калькулятора доходности (по мотивам старого detail_dohodnost.php)

$this->setFrameMode(true);

// 1. Применяем параметры из переменных SEF
// Внимание: компонент catalog уже распарсил URL и положил переменные в $arResult["VARIABLES"]
$sectionCode = $arResult["VARIABLES"]["SECTION_CODE"] ?? '';
$elementCode = $arResult["VARIABLES"]["ELEMENT_CODE"] ?? '';

// Если передан SECTION_CODE, но не передан ID, компонент сам не всегда это подхватывает для элемента
// Но мы используем ELEMENT_CODE, это главное.

// 2. Логика получения элемента для заголовков (SEO)
// В старом файле мы делали запрос к БД, чтобы установить заголовок "Доходность ..."
// Здесь мы можем использовать тот же подход, либо довериться компоненту catalog.element.
// Однако, catalog.element установит стандартный заголовок товара.
// Нам нужно "Доходность [Имя товара]".

// Подключаем модуль инфоблоков
if (\Bitrix\Main\Loader::includeModule('iblock')) {
    $element = \Bitrix\Iblock\ElementTable::getList([
        'select' => ['ID', 'IBLOCK_ID', 'NAME'],
        'filter' => [
            '=CODE' => $elementCode,
            'IBLOCK_ID' => $arParams['IBLOCK_ID'], // Используем ID из параметров компонента
        ],
        'limit' => 1,
    ])->fetch();

    if ($element) {
        $productNamePrefix = "Доходность ";
        $pageTitle = $productNamePrefix . $element['NAME'];
        $pageDescription = "Калькулятор прибыльности асика " . $element['NAME'] . " - GIS-MINING 2025";

        // Устанавливаем заголовки
        $APPLICATION->SetTitle($pageTitle);
        $APPLICATION->SetPageProperty("title", $pageTitle);
        $APPLICATION->SetPageProperty("description", $pageDescription);

        // Доп классы из старого файла
        $APPLICATION->SetPageProperty("header_right_class", "color-block");
        $APPLICATION->SetPageProperty("main_class", "product-page");
    }
}
?>

<div class="catalog-page catalog-new product-page container" id="app-root">
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.element",
        "catalog_element_dohodnost", // Используем специфический шаблон
        array_merge($arParams, [
            "ELEMENT_CODE" => $elementCode,
            "SECTION_CODE" => $sectionCode,

            // Переопределяем параметры URL для калькулятора, чтобы ссылки внутри вели правильно (если шаблон их использует)
            // Но в данном случае мы находимся на странице калькулятора.
    
            // Настройки SEO из старого файла (отключаем стандартные, так как установили выше свои)
            "SET_TITLE" => "N",
            "SET_BROWSER_TITLE" => "N",
            "SET_META_KEYWORDS" => "N",
            "SET_META_DESCRIPTION" => "N",
            "SET_CANONICAL_URL" => "N",
            "ADD_SECTIONS_CHAIN" => "N", // Можно включить, если нужно, но в старом было N
            "ADD_ELEMENT_CHAIN" => "N",

            // Специфические поля из старого файла
            "PROPERTY_CODE" => [
                "MANUFACTURER",
                "CRYPTO",
                "ALGORITHM",
                "HASHRATE",
                "EFFICIENCY",
                "POWER",
                "GUARANTEE",
                "HIT",
                "MORE_PHOTO"
            ],

            // Обработка 404
            "SET_STATUS_404" => "Y",
            "SHOW_404" => "Y",
            "FILE_404" => "/404.php",
        ]),
        $component,
        ["HIDE_ICONS" => "Y"]
    );
    ?>
</div>
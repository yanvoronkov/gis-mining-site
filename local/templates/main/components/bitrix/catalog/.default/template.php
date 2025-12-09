<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

// Этот файл является маршрутизатором комплексного компонента
// Реальная маршрутизация происходит автоматически на основе SEF_URL_TEMPLATES
// Здесь мы только подключаем необходимые ресурсы

$this->setFrameMode(true);

// ОТЛАДКА: Показываем какую страницу выбрал компонент
// if (isset($_GET['debug'])) { ... }

if (!$templatePage) {
    $templatePage = "sections";
}

// ИСПРАВЛЕНИЕ МАРШРУТИЗАЦИИ:
// Если компонент ошибся и определил страницу как 'section', но в URL есть код элемента или фильтра,
// мы принудительно меняем страницу.
if (isset($_REQUEST["ELEMENT_CODE"]) && !empty($_REQUEST["ELEMENT_CODE"])) {
    $templatePage = "element";
    $arResult["VARIABLES"]["ELEMENT_CODE"] = $_REQUEST["ELEMENT_CODE"];
    $arResult["VARIABLES"]["SECTION_CODE"] = $_REQUEST["SECTION_CODE"] ?? $_REQUEST["SECTION_CODE_PATH"] ?? "";
} elseif (isset($_REQUEST["SMART_FILTER_PATH"]) && !empty($_REQUEST["SMART_FILTER_PATH"])) {
    $templatePage = "smart_filter";
    $arResult["VARIABLES"]["SMART_FILTER_PATH"] = $_REQUEST["SMART_FILTER_PATH"];
    $arResult["VARIABLES"]["SECTION_CODE"] = $_REQUEST["SECTION_CODE"] ?? $_REQUEST["SECTION_CODE_PATH"] ?? "";
}

$this->IncludeComponentTemplate($templatePage);

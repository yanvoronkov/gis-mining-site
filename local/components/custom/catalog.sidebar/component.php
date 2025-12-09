<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main\Loader;

/**
 * Компонент catalog.sidebar
 * Универсальный сайдбар для каталога с навигацией по инфоблокам и умным фильтром
 */

// Параметры компонента
$IBLOCK_ID = intval($arParams['IBLOCK_ID']);
$SHOW_FILTER = $arParams['SHOW_FILTER'] === 'Y';
$FILTER_NAME = $arParams['FILTER_NAME'] ?: 'arrFilter';
$SEF_RULE = $arParams['SEF_RULE'] ?: '';
$SECTION_ID = intval($arParams['SECTION_ID'] ?: 0);

// Проверяем наличие IBLOCK_ID
if ($IBLOCK_ID <= 0) {
    ShowError('Не указан IBLOCK_ID для компонента catalog.sidebar');
    return;
}

// Загружаем модуль инфоблоков
if (!Loader::includeModule('iblock')) {
    ShowError('Не удалось загрузить модуль iblock');
    return;
}

// Передаем параметры в шаблон
$arResult['IBLOCK_ID'] = $IBLOCK_ID;
$arResult['SHOW_FILTER'] = $SHOW_FILTER;
$arResult['FILTER_NAME'] = $FILTER_NAME;
$arResult['SEF_RULE'] = $SEF_RULE;
$arResult['SECTION_ID'] = $SECTION_ID;

// Подключаем шаблон
$this->IncludeComponentTemplate();

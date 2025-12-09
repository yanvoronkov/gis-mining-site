<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arResult */
/** @var array $arParams */
/** @global CMain $APPLICATION */

// Получаем текущий код раздела и ID инфоблока
$sectionCode = $arResult["CURRENT_SECTION_CODE"] ?? "";
$iblockId = $arResult["CURRENT_IBLOCK_ID"] ?? 1;

// Если мы на главной странице каталога (нет кода раздела в переменных), то выходим.
// Здесь SEO управляется через свойства папки.
if (empty($arResult["VARIABLES"]["SECTION_CODE"]) && empty($sectionCode)) {
    return;
}

// SEO-тексты теперь устанавливаются стандартными механизмами Битрикс (через настройки инфоблока и разделов)
// Компонент catalog.section настроен на установку заголовков и мета-тегов.

// --- АВТОМАТИЧЕСКАЯ ГЕНЕРАЦИЯ OPEN GRAPH И TWITTER CARDS ---
// Берем значения из стандартных SEO-свойств страницы, которые установил компонент
$pageTitle = $APPLICATION->GetPageProperty("title") ?: $APPLICATION->GetTitle();
$pageDescription = $APPLICATION->GetPageProperty("description");
$pageUrl = $APPLICATION->GetCurPage(false);

$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;

// Изображение по умолчанию
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.png';

// Пытаемся найти изображение раздела, если оно есть в arResult
if (!empty($arResult["PICTURE"]["SRC"])) {
    $ogImageUrl = $protocol . '://' . $serverName . $arResult["PICTURE"]["SRC"];
} elseif (!empty($arResult["DETAIL_PICTURE"]["SRC"])) {
    $ogImageUrl = $protocol . '://' . $serverName . $arResult["DETAIL_PICTURE"]["SRC"];
}

// Устанавливаем OG-теги
$APPLICATION->SetPageProperty("OG:TITLE", $pageTitle);
$APPLICATION->SetPageProperty("OG:DESCRIPTION", $pageDescription);
$APPLICATION->SetPageProperty("OG:TYPE", "website");
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// Устанавливаем Twitter Card
$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", $pageTitle);
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", $pageDescription);
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);
// -----------------------------------------------------------


// (Код ручной установки SEO от инфоблока удален для использования стандартных механизмов Битрикс)
?>
<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arResult */
/** @global CMain $APPLICATION */
global $APPLICATION;

// Получаем данные из глобальной переменной
global $arResult;
if (empty($arResult)) {
    // Попробуем получить данные из компонента, если они доступны
    if (isset($this) && !empty($this->arResult)) {
        $arResult = $this->arResult;
    }
}

// Если по какой-то причине $arResult пуст, ничего не делаем
if (empty($arResult) || !isset($arResult['NAME'])) {
    return;
}

// Используем стандартные SEO-функции Битрикса
// Битрикс автоматически установит метатеги на основе настроек инфоблока

// Устанавливаем заголовок страницы (H1)
$APPLICATION->SetTitle($arResult['NAME']);

// Определяем раздел каталога на основе IBLOCK_ID товара
$catalogSection = '';
$catalogUrl = '';

switch ($arResult['IBLOCK_ID']) {
    case 1: // ASIC-майнеры
        $catalogSection = 'ASIC-майнеры';
        $catalogUrl = '/catalog/asics/';
        break;
    case 3: // GPU-майнеры
        $catalogSection = 'GPU-майнеры';
        $catalogUrl = '/catalog/gpu/';
        break;
    case 11: // Видеокарты
        $catalogSection = 'Видеокарты';
        $catalogUrl = '/catalog/videocard/';
        break;
    case 5: // Контейнеры
        $catalogSection = 'Контейнеры';
        $catalogUrl = '/catalog/conteynery/';
        break;
    case 6: // Готовый бизнес
        $catalogSection = 'Готовый бизнес';
        $catalogUrl = '/catalog/gotovyy-biznes/';
        break;
    case 7: // Инвестиции
        $catalogSection = 'Инвестиции';
        $catalogUrl = '/catalog/investicii/';
        break;
    default:
        // По умолчанию используем "Каталог"
        $catalogSection = 'Каталог';
        $catalogUrl = '/catalog/';
}

// Проверяем URL страницы, чтобы понять, нужно ли добавлять раздел каталога
$currentUrl = $APPLICATION->GetCurDir();
$needToAddSection = true;

// Если URL содержит путь к разделу каталога, значит раздел уже добавлен автоматически
switch ($arResult['IBLOCK_ID']) {
    case 1: // ASIC-майнеры
        if (strpos($currentUrl, '/catalog/asics/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 3: // GPU-майнеры
        if (strpos($currentUrl, '/catalog/gpu/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 11: // Видеокарты
        if (strpos($currentUrl, '/catalog/videocard/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 5: // Контейнеры
        if (strpos($currentUrl, '/catalog/conteynery/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 6: // Готовый бизнес
        if (strpos($currentUrl, '/catalog/gotovyy-biznes/') !== false) {
            $needToAddSection = false;
        }
        break;
    case 7: // Инвестиции
        if (strpos($currentUrl, '/catalog/investicii/') !== false) {
            $needToAddSection = false;
        }
        break;
}

// Добавляем раздел каталога в хлебные крошки только если он еще не добавлен автоматически
if (!empty($catalogSection) && $needToAddSection) {
    $APPLICATION->AddChainItem($catalogSection, $catalogUrl);
}

// Добавляем товар в хлебные крошки
$APPLICATION->AddChainItem($arResult['NAME'], $arResult['DETAIL_PAGE_URL']);

// Устанавливаем Open Graph теги на основе данных товара
$siteName = COption::GetOptionString("main", "site_name", "GIS Mining");

// Определяем полный URL страницы
$protocol = CMain::IsHTTPS() ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$cleanDomain = preg_replace('/:\d+$/', '', $domain);
$fullPageUrl = $protocol . '://' . $cleanDomain . $arResult['DETAIL_PAGE_URL'];

// Формируем URL изображения
$imageUrl = !empty($arResult['DETAIL_PICTURE']['SRC'])
    ? $protocol . '://' . $cleanDomain . $arResult['DETAIL_PICTURE']['SRC']
    : '';

// Open Graph теги
$APPLICATION->SetPageProperty('og:title', $arResult['NAME']);
$APPLICATION->SetPageProperty('og:type', 'product');
$APPLICATION->SetPageProperty('og:url', $fullPageUrl);
if ($imageUrl) {
    $APPLICATION->SetPageProperty('og:image', $imageUrl);
}
$APPLICATION->SetPageProperty('og:site_name', $siteName);

// Twitter Card теги
$APPLICATION->SetPageProperty('twitter:card', 'summary_large_image');
$APPLICATION->SetPageProperty('twitter:title', $arResult['NAME']);
if ($imageUrl) {
    $APPLICATION->SetPageProperty('twitter:image', $imageUrl);
}

// ======================================================================
// PRODUCT SCHEMA (JSON-LD) - Универсальная система
// ======================================================================

$productSchema = [
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $arResult['NAME']
];

// Description
if (!empty($arResult['PREVIEW_TEXT'])) {
    $productSchema['description'] = strip_tags($arResult['PREVIEW_TEXT']);
} elseif (!empty($arResult['DETAIL_TEXT'])) {
    $productSchema['description'] = mb_substr(strip_tags($arResult['DETAIL_TEXT']), 0, 300);
}

// Image
if (!empty($arResult['DETAIL_PICTURE']['SRC'])) {
    $productSchema['image'] = $protocol . '://' . $cleanDomain . $arResult['DETAIL_PICTURE']['SRC'];
}

// Brand (проверяем MANUFACTURER (ASIC) или BRAND (GPU/видеокарты))
if (!empty($arResult['PROPERTIES']['MANUFACTURER']['VALUE'])) {
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $arResult['PROPERTIES']['MANUFACTURER']['VALUE']
    ];
} elseif (!empty($arResult['PROPERTIES']['BRAND']['VALUE'])) {
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $arResult['PROPERTIES']['BRAND']['VALUE']
    ];
} else {
    // Дефолтный бренд по типу каталога
    $defaultBrand = 'GIS Mining';
    switch ($arResult['IBLOCK_ID']) {
        case 1: // ASIC
            $defaultBrand = 'ASIC Miners';
            break;
        case 11: // Видеокарты
            $defaultBrand = 'GPU';
            break;
    }
    $productSchema['brand'] = [
        '@type' => 'Brand',
        'name' => $defaultBrand
    ];
}

// Offers (price and availability)
if (!empty($arResult['PRICES']['BASE'])) {
    $price = $arResult['PRICES']['BASE'];
    $productSchema['offers'] = [
        '@type' => 'Offer',
        'price' => $price['VALUE'],
        'priceCurrency' => $price['CURRENCY'],
        'availability' => $arResult['CAN_BUY'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => $fullPageUrl
    ];
}

// SKU (артикул)
if (!empty($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'])) {
    $productSchema['sku'] = $arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'];
}

// Регистрируем схему в универсальной системе
$schemas = $APPLICATION->GetPageProperty('json_ld_schemas') ?: [];
$schemas['Product'] = $productSchema;
$APPLICATION->SetPageProperty('json_ld_schemas', $schemas);

<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

// Не выводим ничего, если хлебных крошек нет
if(empty($arResult)) {
    return "";
}

// --- НАЧАЛО: Генерируем массив для JSON-LD ---
$itemListElement = [];
$itemCount = count($arResult);

for($index = 0; $index < $itemCount; ++$index) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);
    $link = $arResult[$index]["LINK"];

    // Для последнего элемента крошек тоже делаем ссылку (нужно для SEO)
    $item = ($link <> "") ? 'https://' . $_SERVER['SERVER_NAME'] . $link : '';

    $itemListElement[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'item' => $item // У последнего элемента item может быть пустым
    ];
}
// --- КОНЕЦ: Генерируем массив для JSON-LD ---


// --- ВЫВОДИМ ВИЗУАЛЬНЫЕ ХЛЕБНЫЕ КРОШКИ (стандартный HTML) ---
$strReturn = '<div class="breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">';

for($index = 0; $index < $itemCount; ++$index) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);

    if($arResult[$index]["LINK"] <> "") {
        $strReturn .= '
            <div class="breadcrumbs__item" id="bx_breadcrumb_'.$index.'" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <a href="'.$arResult[$index]["LINK"].'" title="'.$title.'" itemprop="item">
                    <span itemprop="name">'.$title.'</span>
                </a>
                <meta itemprop="position" content="'.($index + 1).'" />
            </div>';
    } else {
        $strReturn .= '
            <div class="breadcrumbs__item">
                <span itemprop="name">'.$title.'</span>
                <meta itemprop="position" content="'.($index + 1).'" />
            </div>';
    }
}
$strReturn .= '</div>';


// --- ВЫВОДИМ СКРИПТ С МИКРОРАЗМЕТКОЙ ---
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $itemListElement
];

// JSON-LD микроразметка убрана - используется общая в header.php
// echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';

// Возвращаем HTML крошек для отображения на странице
return $strReturn;
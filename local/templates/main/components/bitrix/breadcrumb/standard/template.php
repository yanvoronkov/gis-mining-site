<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/**
 * Стандартный шаблон хлебных крошек Битрикса
 * Использует правильную логику с файлами .section.php и AddChainItem()
 */

global $APPLICATION;

// Не выводим ничего, если хлебных крошек нет
if (empty($arResult)) {
    return "";
}

// Проверяем исключенные страницы
$excludePages = array_map('trim', explode(',', $arParams["EXCLUDE_PAGES"] ?: '404.php'));
$currentPageFile = basename($APPLICATION->GetCurPage(false));

if (in_array($currentPageFile, $excludePages)) {
    return "";
}

// Проверяем 404 страницы
if (
    http_response_code() === 404 ||
    (defined('ERROR_404') && ERROR_404 === true) ||
    (isset($APPLICATION->arResult['ERROR_404']) && $APPLICATION->arResult['ERROR_404'] === true)
) {
    return "";
}

// --- ГЕНЕРАЦИЯ JSON-LD МИКРОРАЗМЕТКИ ---
$itemListElement = [];
$itemCount = count($arResult);

for ($index = 0; $index < $itemCount; ++$index) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);
    $link = $arResult[$index]["LINK"];

    // Формируем полный URL для микроразметки
    $item = '';
    if ($link <> "") {
        $protocol = CMain::IsHTTPS() ? "https" : "http";
        $domain = $_SERVER['HTTP_HOST'];
        $cleanDomain = preg_replace('/:\d+$/', '', $domain);
        $item = $protocol . '://' . $cleanDomain . $link;
    }

    $itemListElement[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $title,
        'item' => $item
    ];
}

// --- ВЫВОД СКРИПТА С МИКРОРАЗМЕТКОЙ ---
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $itemListElement
];

// Выводим JSON-LD микроразметку через AddViewContent для корректного размещения перед </body>
$APPLICATION->AddViewContent('before_body_close', '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');

// --- ВЫВОД ВИЗУАЛЬНЫХ ХЛЕБНЫХ КРОШЕК ---
$strReturn = '<nav class="breadcrumbs container" aria-label="Хлебные крошки">';

for ($index = 0; $index < $itemCount; ++$index) {
    $title = htmlspecialcharsex($arResult[$index]["TITLE"]);
    $link = $arResult[$index]["LINK"];
    $isLast = ($index === $itemCount - 1);

    // Выводим элемент (со ссылкой или без)
    if ($link <> "" && !$isLast) {
        // Элемент со ссылкой (не последний)
        $strReturn .= '
            <a href="' . $link . '" class="breadcrumbs__item">
                <span>' . $title . '</span>
            </a>';
    } else {
        // Элемент без ссылки (последний или без ссылки)
        $class = $isLast ? 'breadcrumbs__item breadcrumbs__item--current' : 'breadcrumbs__item';
        $strReturn .= '
            <span class="' . $class . '">
                <span>' . $title . '</span>
            </span>';
    }

    // Разделитель добавляется после всех элементов, кроме последнего
    if (!$isLast && $arParams["SHOW_SEPARATOR"] !== "N") {
        $separator = $arParams["SEPARATOR"] ?: '/';
        $strReturn .= '<span class="breadcrumbs__separator" aria-hidden="true">' . htmlspecialcharsex($separator) . '</span>';
    }
}

$strReturn .= '</nav>';

// Возвращаем HTML крошек для отображения на странице
return $strReturn;
?>

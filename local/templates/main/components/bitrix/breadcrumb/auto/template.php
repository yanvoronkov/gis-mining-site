<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * @global CMain $APPLICATION
 */

global $APPLICATION;

// --- АВТОМАТИЧЕСКОЕ ФОРМИРОВАНИЕ ХЛЕБНЫХ КРОШЕК НА ОСНОВЕ URL ---
$currentDir = $APPLICATION->GetCurDir(false);
$currentPage = $APPLICATION->GetCurPage();

// Проверяем, не является ли это исключенной страницей или страницей 404
$excludePages = array_map('trim', explode(',', $arParams["EXCLUDE_PAGES"] ?: '404.php'));
$currentPageFile = basename($APPLICATION->GetCurPage(false));

// Отладочная информация убрана

// Проверяем исключенные файлы
if (in_array($currentPageFile, $excludePages)) {
    return ""; // Не выводим хлебные крошки на исключенных страницах
}

// Проверяем, не является ли это страницей 404 по HTTP статусу
if (http_response_code() === 404) {
    return ""; // Не выводим хлебные крошки на страницах 404
}

// Дополнительная проверка: если страница не найдена в Битриксе
if ($APPLICATION->GetCurPage(false) !== $APPLICATION->GetCurPage() && 
    !$APPLICATION->GetCurPage(false) && 
    $APPLICATION->GetCurPage() !== '/') {
    return ""; // Не выводим хлебные крошки на несуществующих страницах
}

// Проверка через Битрикс: если это страница 404
if (defined('ERROR_404') && ERROR_404 === true) {
    return ""; // Не выводим хлебные крошки на страницах 404
}

// Проверка через глобальную переменную Битрикса
global $APPLICATION;
if (isset($APPLICATION->arResult['ERROR_404']) && $APPLICATION->arResult['ERROR_404'] === true) {
    return ""; // Не выводим хлебные крошки на страницах 404
}

// Отладочная информация убрана

// Базовые хлебные крошки
$breadcrumbs = [];

// Добавляем главную страницу, если это разрешено
if ($arParams["SHOW_HOME"] !== "N") {
    $breadcrumbs[] = [
        'TITLE' => $arParams["HOME_TEXT"] ?: 'Главная',
        'LINK' => '/',
        'POSITION' => 1
    ];
}

// Анализируем URL и добавляем соответствующие крошки
$urlParts = array_filter(explode('/', trim($currentDir, '/')));

// Если это не главная страница, добавляем промежуточные крошки
if (!empty($urlParts)) {
    $currentPath = '';
    
    foreach ($urlParts as $index => $part) {
        $currentPath .= '/' . $part;
        
        // Определяем название для каждой части пути
        $title = getBreadcrumbTitle($part, $currentPath);
        
        if ($title) {
            $breadcrumbs[] = [
                'TITLE' => $title,
                'LINK' => $currentPath . '/',
                'POSITION' => count($breadcrumbs) + 1
            ];
        }
    }
}

// Если крошек нет, не выводим ничего
if (empty($breadcrumbs)) {
    return "";
}

// Отладочная информация убрана

// --- ГЕНЕРАЦИЯ JSON-LD МИКРОРАЗМЕТКИ ---
$itemListElement = [];

foreach ($breadcrumbs as $index => $crumb) {
    // ВСЕ элементы item должны содержать полный URL
    $item = 'https://' . $_SERVER['SERVER_NAME'] . $crumb['LINK'];
    
    // Отладочная информация убрана
    
    $itemListElement[] = [
        '@type' => 'ListItem',
        'position' => $crumb['POSITION'],
        'name' => $crumb['TITLE'],
        'item' => $item
    ];
}

// --- ВЫВОД СКРИПТА С МИКРОРАЗМЕТКОЙ ---
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $itemListElement
];

// JSON-LD микроразметка убрана - используется общая в header.php
// echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';

// --- ВЫВОД ВИЗУАЛЬНЫХ ХЛЕБНЫХ КРОШЕК ---
$strReturn = '<nav class="breadcrumbs" aria-label="Хлебные крошки" itemscope itemtype="http://schema.org/BreadcrumbList">';

foreach ($breadcrumbs as $index => $crumb) {
    $isLast = ($index === count($breadcrumbs) - 1);
    
    if ($isLast) {
        // Последний элемент (текущая страница)
        $strReturn .= '
            <span class="breadcrumbs__item breadcrumbs__item--current" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <span itemprop="name">' . htmlspecialcharsex($crumb['TITLE']) . '</span>
                <meta itemprop="position" content="' . $crumb['POSITION'] . '" />
            </span>';
    } else {
        // Обычные ссылки
        $strReturn .= '
            <a href="' . $crumb['LINK'] . '" class="breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <span itemprop="name">' . htmlspecialcharsex($crumb['TITLE']) . '</span>
                <meta itemprop="position" content="' . $crumb['POSITION'] . '" />
            </a>';
        
        // Разделитель (кроме последнего элемента)
        if (!$isLast && $arParams["SHOW_SEPARATOR"] !== "N") {
            $separator = $arParams["SEPARATOR"] ?: '/';
            $strReturn .= '<span class="breadcrumbs__separator" aria-hidden="true">' . htmlspecialcharsex($separator) . '</span>';
        }
    }
}

$strReturn .= '</nav>';

// Возвращаем HTML крошек для отображения на странице
return $strReturn;

/**
 * Получает человекочитаемое название для части URL
 */
function getBreadcrumbTitle($part, $fullPath) {
    $titles = [
        // Основные разделы
        'catalog' => 'Каталог',
        'about' => 'О компании',
        'contacts' => 'Контакты',
        'our-blog' => 'Блог',
        'mining-investicii' => 'Инвестиции в майнинг',
        'razmeschenie' => 'Размещение',
        'stroitelstvo' => 'Строительство',
        'oplata-i-dostavka' => 'Оплата и доставка',
        'policy-confidenciales' => 'Политика конфиденциальности',
        
        // Разделы каталога
        'asics' => 'ASIC-майнеры',
        'gpu' => 'GPU-майнеры',
        'conteynery' => 'Контейнеры',
        'gotovyy-biznes' => 'Готовый бизнес',
        'investicii' => 'Инвестиции',
        
        // Дополнительные страницы
        'newstroitelstvo' => 'Строительство',
    ];
    
    // Если есть готовое название в массиве, возвращаем его
    if (isset($titles[$part])) {
        return $titles[$part];
    }
    
    // Проверяем, не является ли это детальной страницей товара или статьи
    $originalTitle = getOriginalTitleFromComponent($part, $fullPath);
    if ($originalTitle) {
        return $originalTitle;
    }
    
    // Если ничего не найдено, возвращаем оригинальное название с заглавной буквы
    return ucfirst(str_replace(['-', '_'], ' ', $part));
}

/**
 * Получает оригинальное название из данных компонента
 */
function getOriginalTitleFromComponent($part, $fullPath) {
    global $APPLICATION;
    
    // Проверяем, не является ли это детальной страницей каталога
    if (strpos($fullPath, '/catalog/') !== false && 
        strpos($fullPath, '/catalog/') === 0 && 
        $part !== 'catalog') {
        
        // Пытаемся получить данные из глобальной переменной $arResult
        global $arResult;
        if (!empty($arResult['NAME'])) {
            return $arResult['NAME'];
        }
        
        // Если глобальной переменной нет, пытаемся получить данные из компонента
        if (isset($GLOBALS['APPLICATION']->arResult['NAME'])) {
            return $GLOBALS['APPLICATION']->arResult['NAME'];
        }
        
        // Пытаемся получить данные из кеша компонента
        $componentData = getComponentDataFromCache($fullPath);
        if (!empty($componentData['NAME'])) {
            return $componentData['NAME'];
        }
    }
    
    // Проверяем, не является ли это детальной страницей блога
    if (strpos($fullPath, '/our-blog/') !== false && 
        strpos($fullPath, '/our-blog/') === 0 && 
        $part !== 'our-blog') {
        
        // Аналогичная логика для статей блога
        global $arResult;
        if (!empty($arResult['NAME'])) {
            return $arResult['NAME'];
        }
        
        if (isset($GLOBALS['APPLICATION']->arResult['NAME'])) {
            return $GLOBALS['APPLICATION']->arResult['NAME'];
        }
        
        $componentData = getComponentDataFromCache($fullPath);
        if (!empty($componentData['NAME'])) {
            return $componentData['NAME'];
        }
    }
    
    return null;
}

/**
 * Пытается получить данные компонента из кеша или базы данных
 */
function getComponentDataFromCache($fullPath) {
    // Пытаемся получить данные из кеша компонента
    $cacheKey = 'breadcrumb_' . md5($fullPath);
    $cache = \Bitrix\Main\Data\Cache::createInstance();
    
    if ($cache->initCache(3600, $cacheKey, '/breadcrumb/')) {
        return $cache->getVars();
    }
    
    // Если кеша нет, пытаемся получить данные из базы
    $data = [];
    
    // Для каталога товаров
    if (strpos($fullPath, '/catalog/') !== false) {
        $elementCode = basename($fullPath, '/');
        if ($elementCode) {
            $rsElement = CIBlockElement::GetList(
                [],
                ['CODE' => $elementCode, 'ACTIVE' => 'Y'],
                false,
                false,
                ['NAME', 'CODE']
            );
            if ($element = $rsElement->GetNext()) {
                $data['NAME'] = $element['NAME'];
            }
        }
    }
    
    // Для статей блога
    if (strpos($fullPath, '/our-blog/') !== false) {
        $elementCode = basename($fullPath, '/');
        if ($elementCode) {
            $rsElement = CIBlockElement::GetList(
                [],
                ['CODE' => $elementCode, 'ACTIVE' => 'Y'],
                false,
                false,
                ['NAME', 'CODE']
            );
            if ($element = $rsElement->GetNext()) {
                $data['NAME'] = $element['NAME'];
            }
        }
    }
    
    // Сохраняем в кеш
    if (!empty($data)) {
        $cache->startDataCache();
        $cache->endDataCache($data);
    }
    
    return $data;
}
?>

<?php
/**
 * Скрипт для отладки работы модуля SEO умного фильтра Lite
 * 
 * Использование:
 * 1. Откройте этот файл в браузере: https://ваш-домен.ru/local/tools/seo_filter_diagnostic.php
 * 2. Перейдите на страницу /catalog/asics/ и примените фильтр CRYPTO=ZEC
 * 3. Скопируйте URL из адресной строки
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

// Проверяем установку модуля
$moduleInstalled = Loader::includeModule('dwstroy.seochpulite');
$iblockInstalled = Loader::includeModule('iblock');

echo '<html><head><meta charset="UTF-8"><title>Отладка SEO Фильтра</title></head><body>';
echo '<h1>Отладка модуля SEO умного фильтра Lite</h1>';

echo '<h2>1. Проверка установки модуля</h2>';
echo '<p>Модуль dwstroy.seochpulite: ' . ($moduleInstalled ? '<span style="color:green">✓ Установлен</span>' : '<span style="color:red">✗ НЕ установлен</span>') . '</p>';
echo '<p>Модуль iblock: ' . ($iblockInstalled ? '<span style="color:green">✓ Установлен</span>' : '<span style="color:red">✗ НЕ установлен</span>') . '</p>';

if (!$moduleInstalled) {
    echo '<p style="color:red"><strong>ОШИБКА:</strong> Модуль SEO фильтра не установлен!</p>';
    echo '</body></html>';
    exit;
}

// Находим инфоблок модуля
echo '<h2>2. Поиск инфоблока модуля</h2>';

$dbIblock = CIBlock::GetList(
    [],
    ['TYPE' => 'dwstroy_seochpulite']
);

$seoIblockId = null;
if ($arIblock = $dbIblock->Fetch()) {
    $seoIblockId = $arIblock['ID'];
    echo '<p>Инфоблок найден:</p>';
    echo '<ul>';
    echo '<li>ID: ' . $arIblock['ID'] . '</li>';
    echo '<li>Название: ' . htmlspecialchars($arIblock['NAME']) . '</li>';
    echo '<li>Код: ' . htmlspecialchars($arIblock['CODE']) . '</li>';
    echo '</ul>';
} else {
    echo '<p style="color:red">Инфоблок модуля НЕ найден!</p>';
}

// Показываем все записи в инфоблоке
if ($seoIblockId) {
    echo '<h2>3. Записи в инфоблоке модуля</h2>';

    $dbElements = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'NAME' => 'ASC'],
        ['IBLOCK_ID' => $seoIblockId, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'NAME', 'CODE', 'IBLOCK_ID', 'ACTIVE']
    );

    echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    echo '<tr style="background: #f0f0f0;">';
    echo '<th>ID</th><th>Название</th><th>Символьный код</th><th>Старая ссылка</th><th>Новая ссылка</th>';
    echo '</tr>';

    $elementsFound = false;
    while ($arElement = $dbElements->GetNext()) {
        $elementsFound = true;

        // Получаем свойства
        $dbProps = CIBlockElement::GetProperty(
            $seoIblockId,
            $arElement['ID'],
            [],
            ['CODE' => ['OLD_LINK', 'NEW_LINK']]
        );

        $oldLink = '';
        $newLink = '';

        while ($arProp = $dbProps->Fetch()) {
            if ($arProp['CODE'] == 'OLD_LINK') {
                $oldLink = $arProp['VALUE'];
            } elseif ($arProp['CODE'] == 'NEW_LINK') {
                $newLink = $arProp['VALUE'];
            }
        }

        echo '<tr>';
        echo '<td>' . $arElement['ID'] . '</td>';
        echo '<td>' . htmlspecialchars($arElement['NAME']) . '</td>';
        echo '<td><code>' . htmlspecialchars($arElement['CODE']) . '</code></td>';
        echo '<td><code>' . htmlspecialchars($oldLink) . '</code></td>';
        echo '<td><code>' . htmlspecialchars($newLink) . '</code></td>';
        echo '</tr>';
    }

    if (!$elementsFound) {
        echo '<tr><td colspan="5" style="text-align:center; color:gray;">Записи не найдены</td></tr>';
    }

    echo '</table>';
}

// Тестирование конкретного URL
echo '<h2>4. Тест фильтра</h2>';
echo '<p>Чтобы протестировать работу фильтра:</p>';
echo '<ol>';
echo '<li>Откройте <a href="/catalog/asics/" target="_blank">/catalog/asics/</a></li>';
echo '<li>Примените фильтр CRYPTO = ZEC через стандартный интерфейс</li>';
echo '<li>Скопируйте URL из адресной строки браузера</li>';
echo '<li>Это будет ваш <strong>"Старая ссылка"</strong> для записи в модуле</li>';
echo '</ol>';

echo '<h3>Примеры правильных URL:</h3>';
echo '<ul>';
echo '<li><strong>Стандартный URL фильтра:</strong> <code>/catalog/asics/filter/crypto-is-zec/apply/</code></li>';
echo '<li><strong>Красивый URL (через модуль):</strong> <code>/catalog/asics/filter-zec-crypto/</code></li>';
echo '</ul>';

// Проверка интеграции в компонентах
echo '<h2>5. Проверка интеграции в компонентах</h2>';

$files = [
    '/local/components/bitrix/catalog/component.php',
    '/local/components/bitrix/catalog.smart.filter/component.php',
    '/local/templates/main/components/bitrix/catalog/tech_catalog/result_modifier.php',
    '/local/templates/main/components/bitrix/catalog.smart.filter/smart_filter/result_modifier.php',
];

echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
echo '<tr style="background: #f0f0f0;"><th>Файл</th><th>Интеграция</th></tr>';

foreach ($files as $file) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file;
    $exists = file_exists($fullPath);

    if ($exists) {
        $content = file_get_contents($fullPath);
        $hasIntegration = (
            strpos($content, 'dwstroy.seochpulite') !== false ||
            strpos($content, 'Dwstroy\\SeoChpuLite') !== false
        );

        $status = $hasIntegration
            ? '<span style="color:green">✓ Интегрирован</span>'
            : '<span style="color:orange">⚠ НЕ интегрирован</span>';
    } else {
        $status = '<span style="color:red">✗ Файл не найден</span>';
    }

    echo '<tr>';
    echo '<td><code>' . htmlspecialchars($file) . '</code></td>';
    echo '<td>' . $status . '</td>';
    echo '</tr>';
}

echo '</table>';

// Рекомендации
echo '<h2>6. Рекомендации</h2>';
echo '<div style="background: #ffffcc; padding: 15px; border-radius: 5px; border-left: 5px solid #ffaa00;">';
echo '<h3>Чтобы фильтр заработал:</h3>';
echo '<ol>';
echo '<li><strong>Определите стандартный URL фильтра</strong><br>';
echo 'Перейдите на /catalog/asics/, включите фильтр CRYPTO=ZEC вручную, скопируйте URL</li>';
echo '<li><strong>Откройте запись "Крипта ZEC" в инфоблоке модуля</strong></li>';
echo '<li><strong>Заполните поля:</strong>';
echo '<ul>';
echo '<li><strong>Символьный код:</strong> <code>zec-crypto</code> (только код, БЕЗ пути)</li>';
echo '<li><strong>Старая ссылка:</strong> <code>/catalog/asics/filter/crypto-is-zec/apply/</code> (стандартный URL)</li>';
echo '<li><strong>Новая ссылка:</strong> <code>/catalog/asics/filter-zec-crypto/</code> (красивый URL)</li>';
echo '<li><strong>Редирект:</strong> 301 Moved permanently</li>';
echo '<li><strong>Галочка "Генерировать все варианты умных ссылок":</strong> ВКЛЮЧИТЬ</li>';
echo '</ul></li>';
echo '<li><strong>Очистите кеш Битрикс</strong><br>';
echo 'Настройки → Производительность → Очистить кеш</li>';
echo '<li><strong>Протестируйте URL:</strong> <a href="/catalog/asics/filter-zec-crypto/" target="_blank">/catalog/asics/filter-zec-crypto/</a></li>';
echo '</ol>';
echo '</div>';

echo '<hr>';
echo '<p style="color: gray; font-size: 12px;">Дата проверки: ' . date('Y-m-d H:i:s') . '</p>';
echo '</body></html>';

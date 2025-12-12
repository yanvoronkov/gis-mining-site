<?php
/**
 * Проверка работы модуля SEO фильтра
 * Добавьте этот код в /local/php_interface/init.php ВРЕМЕННО для отладки
 */

// Логирование запросов к filter-zec-crypto
AddEventHandler("main", "OnBeforeProlog", function () {
    $uri = $_SERVER['REQUEST_URI'];

    // Проверяем только запросы к фильтру
    if (strpos($uri, 'filter-zec-crypto') !== false) {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/.gemini/seo_filter_trace.log';
        $logData = "\n" . str_repeat('=', 80) . "\n";
        $logData .= date('Y-m-d H:i:s') . " - Запрос к фильтру ZEC\n";
        $logData .= str_repeat('=', 80) . "\n";
        $logData .= "Request URI: " . $uri . "\n";
        $logData .= "Query String: " . ($_SERVER['QUERY_STRING'] ?? 'пусто') . "\n";
        $logData .= "\$_GET параметры:\n" . print_r($_GET, true) . "\n";
        $logData .= "\$_REQUEST параметры:\n" . print_r($_REQUEST, true) . "\n";

        // Проверяем, подключен ли модуль
        if (\Bitrix\Main\Loader::includeModule('dwstroy.seochpulite')) {
            $logData .= "✅ Модуль dwstroy.seochpulite ПОДКЛЮЧЕН\n";

            // Проверяем наличие записи в инфоблоке
            if (\Bitrix\Main\Loader::includeModule('iblock')) {
                $dbElement = \CIBlockElement::GetList(
                    [],
                    [
                        'IBLOCK_ID' => 14,
                        'CODE' => '/catalog/asics/filter-zec-crypto/',
                        'ACTIVE' => 'Y'
                    ],
                    false,
                    false,
                    ['ID', 'NAME', 'CODE']
                );

                if ($arElement = $dbElement->Fetch()) {
                    $logData .= "✅ Запись найдена в инфоблоке: ID=" . $arElement['ID'] . ", NAME=" . $arElement['NAME'] . "\n";

                    // Получаем свойства
                    $dbProps = \CIBlockElement::GetProperty(14, $arElement['ID'], [], ['CODE' => ['OLD_URL', 'NEW_URL']]);
                    while ($arProp = $dbProps->Fetch()) {
                        $logData .= "   " . $arProp['CODE'] . " = " . $arProp['VALUE'] . "\n";
                    }
                } else {
                    $logData .= "❌ Запись НЕ найдена в инфоблоке (возможно неправильный CODE)\n";
                    $logData .= "   Ищем по коду: /catalog/asics/filter-zec-crypto/\n";

                    // Попробуем найти по другим вариантам
                    $variants = [
                        'filter-zec-crypto',
                        'zec-crypto',
                        '/catalog/asics/filter-zec-crypto',
                    ];

                    foreach ($variants as $code) {
                        $dbEl = \CIBlockElement::GetList([], ['IBLOCK_ID' => 14, 'CODE' => $code, 'ACTIVE' => 'Y'], false, false, ['ID', 'NAME', 'CODE']);
                        if ($arEl = $dbEl->Fetch()) {
                            $logData .= "   ✅ Найдена запись с кодом: " . $code . " (ID=" . $arEl['ID'] . ")\n";
                        }
                    }
                }
            }
        } else {
            $logData .= "❌ Модуль dwstroy.seochpulite НЕ ПОДКЛЮЧЕН!\n";
        }

        $logData .= str_repeat('=', 80) . "\n";

        file_put_contents($logFile, $logData, FILE_APPEND);
    }
}, 1); // Приоритет 1 - выполнится раньше других обработчиков

// Логирование ПОСЛЕ работы модуля
AddEventHandler("main", "OnEpilog", function () {
    $uri = $_SERVER['REQUEST_URI'];

    if (strpos($uri, 'filter-zec-crypto') !== false) {
        $logFile = $_SERVER['DOCUMENT_ROOT'] . '/.gemini/seo_filter_trace.log';
        $logData = "\n--- ПОСЛЕ ОБРАБОТКИ (OnEpilog) ---\n";
        $logData .= "\$_GET параметры:\n" . print_r($_GET, true) . "\n";
        $logData .= "\$_REQUEST параметры:\n" . print_r($_REQUEST, true) . "\n";
        $logData .= "Глобальный \$arrFilter:\n";

        global $arrFilter;
        if (isset($arrFilter)) {
            $logData .= print_r($arrFilter, true) . "\n";
        } else {
            $logData .= "НЕ УСТАНОВЛЕН\n";
        }

        $logData .= str_repeat('=', 80) . "\n\n";

        file_put_contents($logFile, $logData, FILE_APPEND);
    }
});

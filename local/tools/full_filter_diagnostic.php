<?php
/**
 * Комплексная диагностика модуля SEO умного фильтра Lite
 * Проверяет весь путь обработки запроса filter-zec-crypto
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

$testUrl = '/catalog/asics/filter-zec-crypto/';
$iblockId = 14; // ID инфоблока "ЧПУ"

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Диагностика фильтра ZEC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        h1 {
            color: #333;
            border-bottom: 3px solid #2196F3;
            padding-bottom: 10px;
        }

        h2 {
            color: #555;
            margin-top: 30px;
        }

        .step {
            background: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #2196F3;
        }

        .step-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .success {
            color: #4caf50;
            font-weight: bold;
        }

        .error {
            color: #f44336;
            font-weight: bold;
        }

        .warning {
            color: #ff9800;
            font-weight: bold;
        }

        .info {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }

        table td,
        table th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background: #f0f0f0;
            font-weight: bold;
        }

        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }

        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Диагностика модуля SEO умного фильтра Lite</h1>
        <p><strong>Тестируемый URL:</strong> <code><?= htmlspecialchars($testUrl) ?></code></p>

        <?php
        $errors = [];
        $warnings = [];
        $info = [];

        // ========================================================================
        // ШАГ 1: Проверка подключения модулей
        // ========================================================================
        ?>
        <div class="step">
            <div class="step-title">ШАГ 1: Проверка подключения модулей</div>
            <?php
            $moduleLoaded = Loader::includeModule('dwstroy.seochpulite');
            $iblockLoaded = Loader::includeModule('iblock');
            $catalogLoaded = Loader::includeModule('catalog');

            if ($moduleLoaded) {
                echo '<p class="success">✅ Модуль dwstroy.seochpulite подключен</p>';
            } else {
                echo '<p class="error">❌ Модуль dwstroy.seochpulite НЕ подключен!</p>';
                $errors[] = 'Модуль SEO фильтра не установлен или не активен';
            }

            if ($iblockLoaded) {
                echo '<p class="success">✅ Модуль iblock подключен</p>';
            } else {
                echo '<p class="error">❌ Модуль iblock НЕ подключен!</p>';
                $errors[] = 'Модуль iblock не установлен';
            }

            if ($catalogLoaded) {
                echo '<p class="success">✅ Модуль catalog подключен</p>';
            } else {
                echo '<p class="error">❌ Модуль catalog НЕ подключен!</p>';
                $errors[] = 'Модуль catalog не установлен';
            }
            ?>
        </div>

        <?php if ($moduleLoaded && $iblockLoaded): ?>

            <!-- ================================================================ -->
            <!-- ШАГ 2: Поиск записи в инфоблоке -->
            <!-- ================================================================ -->
            <div class="step">
                <div class="step-title">ШАГ 2: Поиск записи в инфоблоке "ЧПУ"</div>
                <?php
                // Варианты символьного кода для поиска
                $codeVariants = [
                    '/catalog/asics/filter-zec-crypto/',
                    'filter-zec-crypto',
                    'zec-crypto',
                    '/catalog/asics/filter-zec-crypto',
                    'catalog/asics/filter-zec-crypto',
                ];

                $foundElement = null;
                $foundByCode = null;

                echo '<p>Пробуем найти запись по следующим вариантам символьного кода:</p>';
                echo '<table>';
                echo '<tr><th>Вариант кода</th><th>Результат</th></tr>';

                foreach ($codeVariants as $code) {
                    $dbElement = CIBlockElement::GetList(
                        [],
                        [
                            'IBLOCK_ID' => $iblockId,
                            'CODE' => $code,
                            'ACTIVE' => 'Y'
                        ],
                        false,
                        false,
                        ['ID', 'NAME', 'CODE']
                    );

                    if ($arElement = $dbElement->Fetch()) {
                        echo '<tr>';
                        echo '<td><code>' . htmlspecialchars($code) . '</code></td>';
                        echo '<td class="success">✅ Найдено! ID: ' . $arElement['ID'] . ', Название: ' . htmlspecialchars($arElement['NAME']) . '</td>';
                        echo '</tr>';

                        if (!$foundElement) {
                            $foundElement = $arElement;
                            $foundByCode = $code;
                        }
                    } else {
                        echo '<tr>';
                        echo '<td><code>' . htmlspecialchars($code) . '</code></td>';
                        echo '<td class="error">❌ Не найдено</td>';
                        echo '</tr>';
                    }
                }

                echo '</table>';

                if ($foundElement) {
                    echo '<div class="info">';
                    echo '<p class="success">✅ Запись найдена через код: <code>' . htmlspecialchars($foundByCode) . '</code></p>';
                    echo '<p><strong>ID:</strong> ' . $foundElement['ID'] . '</p>';
                    echo '<p><strong>Название:</strong> ' . htmlspecialchars($foundElement['NAME']) . '</p>';
                    echo '<p><strong>Символьный код:</strong> <code>' . htmlspecialchars($foundElement['CODE']) . '</code></p>';
                    echo '</div>';
                } else {
                    echo '<p class="error">❌ Запись не найдена ни по одному варианту!</p>';
                    $errors[] = 'Запись "Крипта ZEC" не найдена в инфоблоке';

                    // Показываем все записи в инфоблоке
                    echo '<p class="warning">⚠️ Показываю ВСЕ записи в инфоблоке ID=' . $iblockId . ':</p>';
                    $dbAll = CIBlockElement::GetList(
                        [],
                        ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
                        false,
                        false,
                        ['ID', 'NAME', 'CODE']
                    );

                    echo '<table>';
                    echo '<tr><th>ID</th><th>Название</th><th>Символьный код</th></tr>';
                    while ($arEl = $dbAll->Fetch()) {
                        echo '<tr>';
                        echo '<td>' . $arEl['ID'] . '</td>';
                        echo '<td>' . htmlspecialchars($arEl['NAME']) . '</td>';
                        echo '<td><code>' . htmlspecialchars($arEl['CODE']) . '</code></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
                ?>
            </div>

            <?php if ($foundElement): ?>

                <!-- ================================================================ -->
                <!-- ШАГ 3: Проверка свойств записи -->
                <!-- ================================================================ -->
                <div class="step">
                    <div class="step-title">ШАГ 3: Проверка свойств записи</div>
                    <?php
                    $dbProps = CIBlockElement::GetProperty($iblockId, $foundElement['ID'], [], []);

                    $oldUrl = '';
                    $newUrl = '';
                    $redirect = '';
                    $generateVariants = '';

                    echo '<table>';
                    echo '<tr><th>Код свойства</th><th>Название</th><th>Значение</th></tr>';

                    while ($arProp = $dbProps->Fetch()) {
                        $value = $arProp['VALUE'];
                        if (!empty($arProp['VALUE_ENUM'])) {
                            $value = $arProp['VALUE_ENUM'];
                        }

                        // Обработка массивов
                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }

                        if ($arProp['CODE'] == 'OLD_URL')
                            $oldUrl = $value;
                        if ($arProp['CODE'] == 'NEW_URL')
                            $newUrl = $value;
                        if ($arProp['CODE'] == 'REDIRECT')
                            $redirect = $value;
                        if ($arProp['CODE'] == 'GENERATE_VARIANTS')
                            $generateVariants = $value;

                        $highlight = '';
                        if (in_array($arProp['CODE'], ['OLD_URL', 'NEW_URL', 'REDIRECT', 'GENERATE_VARIANTS'])) {
                            $highlight = ' style="background: yellow;"';
                        }

                        echo '<tr' . $highlight . '>';
                        echo '<td><code>' . htmlspecialchars($arProp['CODE']) . '</code></td>';
                        echo '<td>' . htmlspecialchars($arProp['NAME']) . '</td>';
                        echo '<td><strong>' . htmlspecialchars($value) . '</strong></td>';
                        echo '</tr>';
                    }

                    echo '</table>';

                    // Проверка правильности заполнения
                    echo '<div class="info">';
                    echo '<p><strong>Проверка ключевых полей:</strong></p>';

                    if (empty($oldUrl)) {
                        echo '<p class="error">❌ OLD_URL (Старая ссылка) пустое!</p>';
                        $errors[] = 'Поле "Старая ссылка" не заполнено';
                    } else {
                        if (substr($oldUrl, 0, 1) !== '/') {
                            echo '<p class="error">❌ OLD_URL не начинается с "/" : <code>' . htmlspecialchars($oldUrl) . '</code></p>';
                            $errors[] = 'Старая ссылка должна начинаться с /';
                        } elseif (strpos($oldUrl, '/filter/') === false) {
                            echo '<p class="error">❌ OLD_URL не содержит "/filter/" : <code>' . htmlspecialchars($oldUrl) . '</code></p>';
                            $errors[] = 'Старая ссылка должна содержать /filter/';
                        } elseif (substr($oldUrl, -7) !== '/apply/') {
                            echo '<p class="warning">⚠️ OLD_URL не заканчивается на "/apply/" : <code>' . htmlspecialchars($oldUrl) . '</code></p>';
                            $warnings[] = 'Старая ссылка должна заканчиваться на /apply/';
                        } else {
                            echo '<p class="success">✅ OLD_URL правильный: <code>' . htmlspecialchars($oldUrl) . '</code></p>';
                        }
                    }

                    if (empty($newUrl)) {
                        echo '<p class="error">❌ NEW_URL (Новая ссылка) пустое!</p>';
                        $errors[] = 'Поле "Новая ссылка" не заполнено';
                    } else {
                        if ($newUrl === $testUrl) {
                            echo '<p class="success">✅ NEW_URL совпадает с тестируемым: <code>' . htmlspecialchars($newUrl) . '</code></p>';
                        } else {
                            echo '<p class="warning">⚠️ NEW_URL не совпадает с тестируемым URL!</p>';
                            echo '<p>Ожидалось: <code>' . htmlspecialchars($testUrl) . '</code></p>';
                            echo '<p>Получено: <code>' . htmlspecialchars($newUrl) . '</code></p>';
                            $warnings[] = 'NEW_URL не совпадает с тестируемым URL';
                        }
                    }

                    echo '</div>';
                    ?>
                </div>

                <!-- ================================================================ -->
                <!-- ШАГ 4: Симуляция работы модуля -->
                <!-- ================================================================ -->
                <div class="step">
                    <div class="step-title">ШАГ 4: Симуляция парсинга OLD_URL</div>
                    <?php
                    if (!empty($oldUrl)) {
                        echo '<p>Парсим OLD_URL для определения параметров фильтра:</p>';
                        echo '<p><strong>OLD_URL:</strong> <code>' . htmlspecialchars($oldUrl) . '</code></p>';

                        // Извлекаем SMART_FILTER_PATH
                        if (preg_match('#/filter/(.+?)/apply/?#', $oldUrl, $matches)) {
                            $smartFilterPath = $matches[1];
                            echo '<p class="success">✅ Извлечён SMART_FILTER_PATH: <code>' . htmlspecialchars($smartFilterPath) . '</code></p>';

                            // Парсим параметры фильтра
                            $filterParts = explode('/', $smartFilterPath);
                            echo '<p><strong>Части фильтра:</strong></p>';
                            echo '<ul>';
                            foreach ($filterParts as $part) {
                                echo '<li><code>' . htmlspecialchars($part) . '</code></li>';
                            }
                            echo '</ul>';

                            // Разбираем первую часть (должно быть property-operator-value)
                            if (!empty($filterParts[0])) {
                                $partParsed = explode('-', $filterParts[0]);
                                if (count($partParsed) >= 3) {
                                    $property = $partParsed[0];
                                    $operator = $partParsed[1];
                                    $value = implode('-', array_slice($partParsed, 2));

                                    echo '<div class="info">';
                                    echo '<p><strong>Распарсенные параметры:</strong></p>';
                                    echo '<ul>';
                                    echo '<li><strong>Свойство:</strong> <code>' . htmlspecialchars($property) . '</code></li>';
                                    echo '<li><strong>Оператор:</strong> <code>' . htmlspecialchars($operator) . '</code></li>';
                                    echo '<li><strong>Значение:</strong> <code>' . htmlspecialchars($value) . '</code></li>';
                                    echo '</ul>';
                                    echo '</div>';

                                    // Проверяем, существует ли такое свойство
                                    $dbProp = CIBlockProperty::GetList(
                                        [],
                                        ['IBLOCK_ID' => IBLOCK_CATALOG_ASICS, 'CODE' => strtoupper($property)]
                                    );

                                    if ($arProp = $dbProp->Fetch()) {
                                        echo '<p class="success">✅ Свойство <code>' . htmlspecialchars(strtoupper($property)) . '</code> существует в инфоблоке ASICS</p>';
                                        echo '<p>Название: ' . htmlspecialchars($arProp['NAME']) . '</p>';
                                    } else {
                                        echo '<p class="error">❌ Свойство <code>' . htmlspecialchars(strtoupper($property)) . '</code> НЕ найдено в инфоблоке ASICS!</p>';
                                        $errors[] = 'Свойство фильтра не существует в инфоблоке';
                                    }
                                } else {
                                    echo '<p class="error">❌ Не удалось распарсить часть фильтра: <code>' . htmlspecialchars($filterParts[0]) . '</code></p>';
                                    $errors[] = 'Неправильный формат параметра фильтра';
                                }
                            }
                        } else {
                            echo '<p class="error">❌ Не удалось извлечь SMART_FILTER_PATH из OLD_URL!</p>';
                            $errors[] = 'Неправильный формат OLD_URL';
                        }
                    } else {
                        echo '<p class="error">❌ OLD_URL пустой, симуляция невозможна</p>';
                    }
                    ?>
                </div>

                <!-- ================================================================ -->
                <!-- ШАГ 5: Проверка интеграции модуля в компонентах -->
                <!-- ================================================================ -->
                <div class="step">
                    <div class="step-title">ШАГ 5: Проверка интеграции в компонентах</div>
                    <?php
                    $filesToCheck = [
                        '/local/components/bitrix/catalog/component.php' => 'Компонент catalog',
                        '/local/components/bitrix/catalog.smart.filter/component.php' => 'Компонент smart.filter',
                        '/local/templates/main/components/bitrix/catalog/tech_catalog/result_modifier.php' => 'Result modifier каталога',
                        '/local/templates/main/components/bitrix/catalog.smart.filter/smart_filter/result_modifier.php' => 'Result modifier фильтра',
                    ];

                    echo '<table>';
                    echo '<tr><th>Файл</th><th>Статус интеграции</th></tr>';

                    foreach ($filesToCheck as $file => $name) {
                        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file;
                        $exists = file_exists($fullPath);

                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($name) . '<br><code style="font-size: 11px;">' . htmlspecialchars($file) . '</code></td>';

                        if ($exists) {
                            $content = file_get_contents($fullPath);
                            $hasIntegration = (
                                strpos($content, 'dwstroy.seochpulite') !== false ||
                                strpos($content, 'Dwstroy\\SeoChpuLite') !== false
                            );

                            if ($hasIntegration) {
                                echo '<td class="success">✅ Интегрирован</td>';
                            } else {
                                echo '<td class="warning">⚠️ НЕ интегрирован</td>';
                                $warnings[] = $name . ' не содержит кода модуля';
                            }
                        } else {
                            echo '<td class="error">❌ Файл не найден</td>';
                            $warnings[] = $name . ' не существует';
                        }

                        echo '</tr>';
                    }

                    echo '</table>';
                    ?>
                </div>

            <?php endif; // if ($foundElement) ?>
        <?php endif; // if ($moduleLoaded && $iblockLoaded) ?>

        <!-- ================================================================ -->
        <!-- РЕЗЮМЕ -->
        <!-- ================================================================ -->
        <div class="step">
            <div class="step-title">📊 РЕЗЮМЕ ДИАГНОСТИКИ</div>

            <?php if (empty($errors) && empty($warnings)): ?>
                <p class="success">✅ Все проверки пройдены успешно!</p>
                <p>Если фильтр всё ещё не работает, проблема может быть в самом модуле.</p>
                <p><strong>Рекомендации:</strong></p>
                <ul>
                    <li>Очистите кеш Битрикс</li>
                    <li>Пересохраните запись в инфоблоке</li>
                    <li>Проверьте логи PHP на наличие ошибок</li>
                </ul>
            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <h3 class="error">❌ Найдены критические ошибки:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li class="error"><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($warnings)): ?>
                    <h3 class="warning">⚠️ Найдены предупреждения:</h3>
                    <ul>
                        <?php foreach ($warnings as $warning): ?>
                            <li class="warning"><?= htmlspecialchars($warning) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h3>💡 Рекомендации по исправлению:</h3>
                <ol>
                    <?php foreach ($errors as $error): ?>
                        <?php if (strpos($error, 'не найдена') !== false): ?>
                            <li>Откройте инфоблок "ЧПУ" и создайте/исправьте запись "Крипта ZEC"</li>
                        <?php elseif (strpos($error, 'Старая ссылка') !== false): ?>
                            <li>Заполните поле "Старая ссылка" правильным URL:
                                <code>/catalog/asics/filter/crypto-is-zec/apply/</code>
                            </li>
                        <?php elseif (strpos($error, 'не установлен') !== false): ?>
                            <li>Установите и активируйте модуль SEO умного фильтра Lite</li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <li>Очистите кеш Битрикс: Настройки → Производительность → Очистить кеш</li>
                    <li>Проверьте работу фильтра снова</li>
                </ol>
            <?php endif; ?>
        </div>

        <hr>
        <p style="color: gray; font-size: 12px;">Дата диагностики: <?= date('Y-m-d H:i:s') ?></p>
    </div>
</body>

</html>
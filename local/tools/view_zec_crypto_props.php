<?php
/**
 * Просмотр свойств записи "Крипта ZEC" в инфоблоке модуля SEO фильтра
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

Loader::includeModule('iblock');

$IBLOCK_ID = 14; // ID инфоблока "ЧПУ" (dwstroy_seochpulite)
$ELEMENT_ID = 99673; // ID записи "Крипта ZEC"

echo '<html><head><meta charset="UTF-8"><title>Свойства записи "Крипта ZEC"</title></head><body>';
echo '<h1>Свойства записи "Крипта ZEC" (ID: ' . $ELEMENT_ID . ')</h1>';

// Получаем элемент
$dbElement = CIBlockElement::GetByID($ELEMENT_ID);
if ($arElement = $dbElement->GetNext()) {
    echo '<h2>1. Основные поля элемента</h2>';
    echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    echo '<tr style="background: #f0f0f0;"><th>Поле</th><th>Значение</th></tr>';

    $mainFields = [
        'ID' => 'ID',
        'NAME' => 'Название',
        'CODE' => 'Символьный код',
        'ACTIVE' => 'Активность',
        'SORT' => 'Сортировка',
        'PREVIEW_TEXT' => 'Анонс',
        'DETAIL_TEXT' => 'Детальное описание',
    ];

    foreach ($mainFields as $code => $name) {
        echo '<tr>';
        echo '<td><strong>' . $name . '</strong></td>';
        echo '<td>' . htmlspecialchars($arElement[$code]) . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

// Получаем все свойства инфоблока
echo '<h2>2. Свойства инфоблока</h2>';

$dbProps = CIBlockProperty::GetList(
    ['SORT' => 'ASC'],
    ['IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'Y']
);

echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
echo '<tr style="background: #e0e0e0;">';
echo '<th>ID</th><th>Код</th><th>Название</th><th>Тип</th>';
echo '</tr>';

$propertyList = [];

while ($arProp = $dbProps->Fetch()) {
    $propertyList[$arProp['CODE']] = $arProp;

    echo '<tr>';
    echo '<td>' . $arProp['ID'] . '</td>';
    echo '<td><code>' . htmlspecialchars($arProp['CODE']) . '</code></td>';
    echo '<td>' . htmlspecialchars($arProp['NAME']) . '</td>';
    echo '<td>' . htmlspecialchars($arProp['PROPERTY_TYPE']) . '</td>';
    echo '</tr>';
}

echo '</table>';

// Получаем значения свойств для записи "Крипта ZEC"
echo '<h2>3. Значения свойств записи "Крипта ZEC"</h2>';

$dbPropValues = CIBlockElement::GetProperty($IBLOCK_ID, $ELEMENT_ID, [], []);

echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
echo '<tr style="background: #f0f0f0;">';
echo '<th>Код свойства</th><th>Название свойства</th><th>Значение</th>';
echo '</tr>';

$oldLink = '';
$newLink = '';
$redirect = '';
$generateVariants = '';

while ($arPropValue = $dbPropValues->Fetch()) {
    $value = $arPropValue['VALUE'];

    // Обработка множественных значений
    if (is_array($value)) {
        $value = implode(', ', $value);
    }

    // Для списков показываем значение из справочника
    if (!empty($arPropValue['VALUE_ENUM'])) {
        $value = $arPropValue['VALUE_ENUM'];
    }

    $highlight = '';

    // Выделяем важные поля
    if (
        $arPropValue['CODE'] == 'OLD_URL' ||
        $arPropValue['CODE'] == 'NEW_URL' ||
        $arPropValue['CODE'] == 'REDIRECT' ||
        stripos($arPropValue['CODE'], 'старая') !== false ||
        stripos($arPropValue['CODE'], 'новая') !== false ||
        stripos($arPropValue['CODE'], 'ссылка') !== false
    ) {
        $highlight = ' style="background: yellow;"';

        if ($arPropValue['CODE'] == 'OLD_URL' || stripos($arPropValue['CODE'], 'старая') !== false) {
            $oldLink = $value;
        }
        if ($arPropValue['CODE'] == 'NEW_URL' || stripos($arPropValue['CODE'], 'новая') !== false) {
            $newLink = $value;
        }
        if ($arPropValue['CODE'] == 'REDIRECT') {
            $redirect = $value;
        }
    }

    echo '<tr' . $highlight . '>';
    echo '<td><code>' . htmlspecialchars($arPropValue['CODE']) . '</code></td>';
    echo '<td>' . htmlspecialchars($arPropValue['NAME']) . '</td>';
    echo '<td><strong>' . htmlspecialchars($value) . '</strong></td>';
    echo '</tr>';
}

echo '</table>';

// Резюме
echo '<h2>4. Резюме настроек</h2>';

echo '<div style="background: #e7f3ff; padding: 20px; border-left: 5px solid #2196F3; margin: 20px 0;">';
echo '<table border="0" cellpadding="10" style="width: 100%;">';
echo '<tr>';
echo '<td style="width: 30%;"><strong>Старая ссылка (OLD_LINK):</strong></td>';
echo '<td style="background: ' . (empty($oldLink) ? '#ffebee' : '#e8f5e9') . ';">';
echo empty($oldLink)
    ? '<span style="color: red;">❌ НЕ ЗАПОЛНЕНА!</span>'
    : '<code>' . htmlspecialchars($oldLink) . '</code>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><strong>Новая ссылка (NEW_LINK):</strong></td>';
echo '<td style="background: ' . (empty($newLink) ? '#ffebee' : '#e8f5e9') . ';">';
echo empty($newLink)
    ? '<span style="color: red;">❌ НЕ ЗАПОЛНЕНА!</span>'
    : '<code>' . htmlspecialchars($newLink) . '</code>';
echo '</td>';
echo '</tr>';

echo '<tr>';
echo '<td><strong>Редирект:</strong></td>';
echo '<td>';
echo empty($redirect) ? '<em style="color: gray;">Не задан</em>' : htmlspecialchars($redirect);
echo '</td>';
echo '</tr>';
echo '</table>';
echo '</div>';

// Рекомендации
echo '<h2>5. Что делать дальше</h2>';

if (empty($oldLink)) {
    echo '<div style="background: #fff3cd; padding: 20px; border-left: 5px solid #ff9800; margin: 20px 0;">';
    echo '<h3 style="margin-top: 0; color: #ff6f00;">⚠️ ПРОБЛЕМА: Поле "Старая ссылка" пустое!</h3>';
    echo '<p>Модуль НЕ МОЖЕТ работать без заполненного поля "Старая ссылка".</p>';
    echo '<p><strong>Что нужно сделать:</strong></p>';
    echo '<ol>';
    echo '<li>Откройте запись "Крипта ZEC" в админке:<br>';
    echo 'Контент → Информационные блоки → ЧПУ → Крипта ZEC (ID: 99673)</li>';
    echo '<li>Заполните поле "Старая ссылка" правильным URL:<br>';
    echo '<code>/catalog/asics/filter/crypto-is-zec/apply/</code></li>';
    echo '<li>Сохраните запись</li>';
    echo '<li>Очистите кеш: Настройки → Производительность → Очистить кеш</li>';
    echo '</ol>';
    echo '</div>';
} else {
    // Проверяем правильность формата старой ссылки
    $isValidOldLink = true;
    $errors = [];

    // Проверка 1: URL должен начинаться с /
    if (substr($oldLink, 0, 1) !== '/') {
        $isValidOldLink = false;
        $errors[] = 'URL должен начинаться с <code>/</code> (сейчас: <code>' . htmlspecialchars(substr($oldLink, 0, 20)) . '...</code>)';
    }

    // Проверка 2: URL должен содержать /filter/
    if (stripos($oldLink, '/filter/') === false) {
        $isValidOldLink = false;
        $errors[] = 'URL должен содержать <code>/filter/</code>';
    }

    // Проверка 3: URL должен заканчиваться на /apply/
    if (substr($oldLink, -7) !== '/apply/') {
        $isValidOldLink = false;
        $errors[] = 'URL должен заканчиваться на <code>/apply/</code> (сейчас: <code>...' . htmlspecialchars(substr($oldLink, -20)) . '</code>)';
    }

    if (!$isValidOldLink) {
        echo '<div style="background: #ffebee; padding: 20px; border-left: 5px solid #f44336; margin: 20px 0;">';
        echo '<h3 style="margin-top: 0; color: #c62828;">❌ ОШИБКА в поле "Старая ссылка"!</h3>';
        echo '<p><strong>Текущее значение:</strong> <code>' . htmlspecialchars($oldLink) . '</code></p>';
        echo '<p><strong>Найденные ошибки:</strong></p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '<p><strong>Правильный формат:</strong></p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 5px;">/catalog/asics/filter/crypto-is-zec/apply/</pre>';
        echo '<p><strong>Что делать:</strong></p>';
        echo '<ol>';
        echo '<li>Откройте запись в админке: Контент → ЧПУ → Крипта ZEC</li>';
        echo '<li>Исправьте поле "Старая ссылка" на: <code>/catalog/asics/filter/crypto-is-zec/apply/</code></li>';
        echo '<li>Сохраните и очистите кеш</li>';
        echo '</ol>';
        echo '</div>';
    } else {
        echo '<div style="background: #e8f5e9; padding: 20px; border-left: 5px solid #4caf50; margin: 20px 0;">';
        echo '<h3 style="margin-top: 0; color: #2e7d32;">✅ Поле "Старая ссылка" заполнено правильно!</h3>';
        echo '<p><strong>Текущие настройки:</strong></p>';
        echo '<ul>';
        echo '<li>Старая ссылка: <code>' . htmlspecialchars($oldLink) . '</code></li>';
        echo '<li>Новая ссылка: <code>' . htmlspecialchars($newLink) . '</code></li>';
        echo '</ul>';

        echo '<p><strong>Если фильтр всё ещё не работает:</strong></p>';
        echo '<ol>';
        echo '<li>Очистите кеш: Настройки → Производительность → Очистить кеш</li>';
        echo '<li>Откройте <code>' . htmlspecialchars($newLink) . '</code> в браузере</li>';
        echo '<li>Проверьте, что показываются только товары с CRYPTO=ZEC</li>';
        echo '</ol>';

        echo '<p><strong>Если URL не найден (404):</strong></p>';
        echo '<ul>';
        echo '<li>Проверьте, что запись активна (Активность = Да)</li>';
        echo '<li>Проверьте, что модуль активен (Marketplace → Установленные решения)</li>';
        echo '<li>Попробуйте пересохранить запись в админке</li>';
        echo '</ul>';
        echo '</div>';
    }
}

echo '<hr>';
echo '<p style="color: gray; font-size: 12px;">Дата проверки: ' . date('Y-m-d H:i:s') . '</p>';
echo '</body></html>';

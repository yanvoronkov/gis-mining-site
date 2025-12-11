<?php
/**
 * Расширенная диагностика модуля SEO фильтра
 * Поиск ВСЕХ инфоблоков в системе
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;

Loader::includeModule('iblock');

echo '<html><head><meta charset="UTF-8"><title>Поиск инфоблока SEO фильтра</title></head><body>';
echo '<h1>Поиск инфоблока модуля SEO умного фильтра Lite</h1>';

// Ищем все возможные типы инфоблоков
echo '<h2>1. Поиск по всем типам инфоблоков</h2>';

$dbIblockTypes = CIBlockType::GetList(
    ['SORT' => 'ASC'],
    []
);

echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
echo '<tr style="background: #f0f0f0;">';
echo '<th>ID типа</th><th>Название типа</th><th>Язык</th>';
echo '</tr>';

$seoTypes = [];

while ($arType = $dbIblockTypes->Fetch()) {
    $highlight = '';

    // Ищем типы, которые могут быть связаны с модулем
    if (
        stripos($arType['ID'], 'seo') !== false ||
        stripos($arType['ID'], 'chpu') !== false ||
        stripos($arType['ID'], 'filter') !== false ||
        stripos($arType['ID'], 'waltroy') !== false ||
        stripos($arType['ID'], 'dwstroy') !== false ||
        stripos($arType['LANG']['NAME'], 'SEO') !== false ||
        stripos($arType['LANG']['NAME'], 'ЧПУ') !== false ||
        stripos($arType['LANG']['NAME'], 'фильтр') !== false
    ) {
        $highlight = ' style="background: yellow;"';
        $seoTypes[] = $arType['ID'];
    }

    echo '<tr' . $highlight . '>';
    echo '<td><strong>' . htmlspecialchars($arType['ID']) . '</strong></td>';
    echo '<td>' . htmlspecialchars($arType['LANG']['NAME']) . '</td>';
    echo '<td>' . htmlspecialchars($arType['LANG_DIR']) . '</td>';
    echo '</tr>';
}

echo '</table>';

// Ищем инфоблоки в найденных типах
if (!empty($seoTypes)) {
    echo '<h2>2. Инфоблоки в найденных типах (выделены жёлтым)</h2>';

    foreach ($seoTypes as $typeId) {
        $dbIblocks = CIBlock::GetList(
            ['SORT' => 'ASC'],
            ['TYPE' => $typeId]
        );

        echo '<h3>Тип: <code>' . htmlspecialchars($typeId) . '</code></h3>';

        if ($dbIblocks->SelectedRowsCount() > 0) {
            echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
            echo '<tr style="background: #e0e0e0;">';
            echo '<th>ID</th><th>Название</th><th>Код</th><th>Активность</th><th>Кол-во элементов</th>';
            echo '</tr>';

            while ($arIblock = $dbIblocks->Fetch()) {
                // Подсчитываем элементы
                $countElements = CIBlockElement::GetList(
                    [],
                    ['IBLOCK_ID' => $arIblock['ID'], 'ACTIVE' => 'Y'],
                    []
                );

                echo '<tr>';
                echo '<td><strong>' . $arIblock['ID'] . '</strong></td>';
                echo '<td>' . htmlspecialchars($arIblock['NAME']) . '</td>';
                echo '<td><code>' . htmlspecialchars($arIblock['CODE']) . '</code></td>';
                echo '<td>' . ($arIblock['ACTIVE'] == 'Y' ? '✅ Да' : '❌ Нет') . '</td>';
                echo '<td>' . $countElements . '</td>';
                echo '</tr>';

                // Показываем несколько записей для проверки
                echo '<tr>';
                echo '<td colspan="5" style="background: #f9f9f9;">';
                echo '<strong>Записи в этом инфоблоке:</strong><br>';

                $dbElements = CIBlockElement::GetList(
                    ['SORT' => 'ASC'],
                    ['IBLOCK_ID' => $arIblock['ID'], 'ACTIVE' => 'Y'],
                    false,
                    ['nPageSize' => 5],
                    ['ID', 'NAME', 'CODE']
                );

                if ($dbElements->SelectedRowsCount() > 0) {
                    echo '<ul>';
                    while ($arElement = $dbElements->GetNext()) {
                        echo '<li>';
                        echo 'ID: ' . $arElement['ID'] . ' | ';
                        echo 'Название: <strong>' . htmlspecialchars($arElement['NAME']) . '</strong> | ';
                        echo 'Код: <code>' . htmlspecialchars($arElement['CODE']) . '</code>';
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<em style="color: gray;">Записи не найдены</em>';
                }

                echo '</td>';
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo '<p style="color: gray;">Инфоблоков не найдено</p>';
        }
    }
} else {
    echo '<p style="color: orange;">Типы инфоблоков, связанные с SEO фильтром, не найдены автоматически.</p>';
    echo '<p>Показываем ВСЕ типы инфоблоков:</p>';
}

// Показываем ВСЕ инфоблоки в системе
echo '<h2>3. Все инфоблоки в системе</h2>';

$dbAllIblocks = CIBlock::GetList(
    ['SORT' => 'ASC', 'NAME' => 'ASC'],
    []
);

echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
echo '<tr style="background: #f0f0f0;">';
echo '<th>ID</th><th>Тип</th><th>Название</th><th>Код</th><th>Активность</th>';
echo '</tr>';

while ($arIblock = $dbAllIblocks->Fetch()) {
    $highlight = '';

    // Выделяем потенциально нужные инфоблоки
    if (
        stripos($arIblock['NAME'], 'SEO') !== false ||
        stripos($arIblock['NAME'], 'ЧПУ') !== false ||
        stripos($arIblock['NAME'], 'фильтр') !== false ||
        stripos($arIblock['CODE'], 'seo') !== false ||
        stripos($arIblock['CODE'], 'chpu') !== false ||
        stripos($arIblock['CODE'], 'filter') !== false
    ) {
        $highlight = ' style="background: lightgreen;"';
    }

    echo '<tr' . $highlight . '>';
    echo '<td><strong>' . $arIblock['ID'] . '</strong></td>';
    echo '<td><code>' . htmlspecialchars($arIblock['IBLOCK_TYPE_ID']) . '</code></td>';
    echo '<td>' . htmlspecialchars($arIblock['NAME']) . '</td>';
    echo '<td><code>' . htmlspecialchars($arIblock['CODE']) . '</code></td>';
    echo '<td>' . ($arIblock['ACTIVE'] == 'Y' ? '✅' : '❌') . '</td>';
    echo '</tr>';
}

echo '</table>';

echo '<hr>';
echo '<h2>4. Рекомендации</h2>';
echo '<div style="background: #ffffdd; padding: 15px; border-left: 5px solid #ffbb00;">';
echo '<p><strong>Найдите в таблице выше инфоблок с названием похожим на:</strong></p>';
echo '<ul>';
echo '<li>"SEO умного фильтра Lite"</li>';
echo '<li>"ЧПУ фильтра"</li>';
echo '<li>Или любой другой с кодом типа, содержащим "seo", "chpu", "filter", "waltroy", "dwstroy"</li>';
echo '</ul>';
echo '<p><strong>Если нашли - запомните:</strong></p>';
echo '<ul>';
echo '<li>ID инфоблока</li>';
echo '<li>Тип инфоблока</li>';
echo '</ul>';
echo '<p><strong>Затем:</strong></p>';
echo '<ol>';
echo '<li>Откройте этот инфоблок в админке: Контент → Информационные блоки</li>';
echo '<li>Создайте или отредактируйте запись "Крипта ZEC"</li>';
echo '<li>Заполните поля согласно инструкции</li>';
echo '</ol>';
echo '</div>';

echo '<hr>';
echo '<p style="color: gray; font-size: 12px;">Дата проверки: ' . date('Y-m-d H:i:s') . '</p>';
echo '</body></html>';

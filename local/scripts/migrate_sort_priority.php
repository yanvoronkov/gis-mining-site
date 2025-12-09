<?php
/**
 * Скрипт миграции для добавления свойства SORT_PRIORITY и заполнения его значений.
 * Запускать из командной строки или браузера (под админом).
 */

if (empty($_SERVER["DOCUMENT_ROOT"])) {
    $_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__, 2);
}

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock') || !Loader::includeModule('catalog')) {
    die("Modules iblock or catalog not installed");
}

// Подключаем наши функции из init.php (они уже должны быть там)
// require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/init.php'; 
// init.php подключается автоматически в прологе

global $USER;
if (!is_object($USER))
    $USER = new CUser;

echo "Start migration...\n";

// Инфоблоки для обработки
$iblockIds = [IBLOCK_CATALOG_ASICS, IBLOCK_CATALOG_VIDEOCARD];

// 1. Создаем свойство если его нет
foreach ($iblockIds as $iblockId) {
    if ($iblockId <= 0)
        continue;

    $res = CIBlockProperty::GetList([], ["IBLOCK_ID" => $iblockId, "CODE" => "SORT_PRIORITY"]);
    if ($prop = $res->Fetch()) {
        echo "Property SORT_PRIORITY already exists in IBLOCK {$iblockId} (ID: {$prop['ID']})\n";
    } else {
        $arFields = [
            "NAME" => "Приоритет сортировки",
            "ACTIVE" => "Y",
            "SORT" => "100",
            "CODE" => "SORT_PRIORITY",
            "PROPERTY_TYPE" => "N", // Число
            "IBLOCK_ID" => $iblockId,
            "FILTRABLE" => "Y", // Чтобы можно было сортировать
        ];
        $ibp = new CIBlockProperty;
        $propId = $ibp->Add($arFields);
        if ($propId > 0) {
            echo "Created property SORT_PRIORITY inside IBLOCK {$iblockId} (ID: {$propId})\n";
        } else {
            echo "Error creating property in IBLOCK {$iblockId}: " . $ibp->LAST_ERROR . "\n";
        }
    }
}

// 2. Обновляем все товары
foreach ($iblockIds as $iblockId) {
    echo "Processing IBLOCK {$iblockId}...\n";

    $res = CIBlockElement::GetList(
        ["ID" => "ASC"],
        ["IBLOCK_ID" => $iblockId],
        false,
        false,
        ["ID", "IBLOCK_ID"]
    );

    $count = 0;
    while ($arItem = $res->Fetch()) {
        // Вызываем нашу функцию из init.php
        updateProductSortPriority($arItem['ID'], $arItem['IBLOCK_ID']);
        $count++;
        if ($count % 50 == 0) {
            echo "Processed {$count} items...\n";
        }
    }
    echo "Done IBLOCK {$iblockId}. Total {$count} items.\n";
}

echo "Migration completed.\n";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

header('Content-Type: application/json; charset=utf-8');

$API_KEY_EXPECTED = "supersecret123";
$IBLOCK_ID = 1; // твой инфоблок

if (($_POST['API_KEY'] ?? '') !== $API_KEY_EXPECTED) {
    http_response_code(403);
    echo json_encode(["error" => "wrong API_KEY"]);
    exit;
}

if (!Loader::includeModule('iblock')) {
    http_response_code(500);
    echo json_encode(["error" => "iblock not loaded"]);
    exit;
}

function makeFileFromUpload($arr) {
    if (empty($arr) || !isset($arr['tmp_name']) || $arr['error'] != 0 || $arr['tmp_name'] === '') {
        return null;
    }
    $fa = CFile::MakeFileArray($arr['tmp_name']);
    $fa['name'] = $arr['name'];
    $fa['type'] = $arr['type'];
    $fa['MODULE_ID'] = 'iblock';
    return $fa;
}

$NAME       = trim((string)($_POST['NAME'] ?? ''));
$CODE       = trim((string)($_POST['CODE'] ?? ''));
$SECTION_ID = (int)($_POST['SECTION_ID'] ?? 0);

// ВАЖНО: PREVIEW_TEXT идёт в анонс (HTML-обёртка приходит от бота)
$PREVIEW_TEXT       = (string)($_POST['PREVIEW_TEXT'] ?? '');
$PREVIEW_TEXT_TYPE  = (string)($_POST['PREVIEW_TEXT_TYPE'] ?? 'html');

// DETAIL_TEXT — без дублирования div, тип текст
$DETAIL_TEXT        = (string)($_POST['DETAIL_TEXT'] ?? '');
$DETAIL_TEXT_TYPE   = (string)($_POST['DETAIL_TEXT_TYPE'] ?? 'text');

$fields = [
    'IBLOCK_ID'          => $IBLOCK_ID,
    'ACTIVE'             => 'Y',
    'NAME'               => $NAME,
    'CODE'               => $CODE,
    'PREVIEW_TEXT'       => $PREVIEW_TEXT,
    'PREVIEW_TEXT_TYPE'  => $PREVIEW_TEXT_TYPE, // html
    'DETAIL_TEXT'        => $DETAIL_TEXT,
    'DETAIL_TEXT_TYPE'   => $DETAIL_TEXT_TYPE,  // text
];

if ($SECTION_ID > 0) {
    $fields['IBLOCK_SECTION_ID'] = $SECTION_ID;
}

// Картинки анонса и детальная
if (isset($_FILES['PREVIEW_PICTURE'])) {
    $fa = makeFileFromUpload($_FILES['PREVIEW_PICTURE']);
    if ($fa) { $fields['PREVIEW_PICTURE'] = $fa; }
}
if (isset($_FILES['DETAIL_PICTURE'])) {
    $fa = makeFileFromUpload($_FILES['DETAIL_PICTURE']);
    if ($fa) { $fields['DETAIL_PICTURE'] = $fa; }
}

// Свойства (кроме файлов)
$props = [];
foreach ($_POST as $k => $v) {
    if (strncmp($k, 'PROPERTY_', 9) === 0) {
        $code = substr($k, 9);
        // Для типа список L ожидается ID варианта (бот теперь шлёт ID)
        $props[$code] = $v;
    }
}

$el = new CIBlockElement();
$PRODUCT_ID = $el->Add($fields);
if (!$PRODUCT_ID) {
    http_response_code(500);
    echo json_encode(['error' => 'add failed', 'bitrix_error' => $el->LAST_ERROR], JSON_UNESCAPED_UNICODE);
    exit;
}

// Применяем свойства (простые)
if (!empty($props)) {
    CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $IBLOCK_ID, $props);
}

// MORE_PHOTO[] — множественное файловое свойство
if (isset($_FILES['MORE_PHOTO'])) {
    $files = $_FILES['MORE_PHOTO'];
    if (is_array($files['tmp_name'])) {
        $vals = [];
        foreach ($files['tmp_name'] as $i => $tmp) {
            if ($files['error'][$i] != 0 || $tmp === '') { continue; }
            $fa = CFile::MakeFileArray($tmp);
            $fa['name'] = $files['name'][$i];
            $fa['type'] = $files['type'][$i];
            $fa['MODULE_ID'] = 'iblock';
            $vals['n'.$i] = ['VALUE' => $fa, 'DESCRIPTION' => ''];
        }
        if (!empty($vals)) {
            CIBlockElement::SetPropertyValuesEx($PRODUCT_ID, $IBLOCK_ID, ['MORE_PHOTO' => $vals]);
        }
    }
}

// Цена/количество (если установлен catalog)
if (Loader::includeModule('catalog')) {
    $quantity = (float)($_POST['QUANTITY'] ?? 0);
    CCatalogProduct::Add(['ID' => $PRODUCT_ID, 'QUANTITY' => $quantity]);

    if (isset($_POST['PRICE'])) {
        $price = (float)$_POST['PRICE'];
        $currency = $_POST['CURRENCY'] ?? 'RUB';
        $base = CCatalogGroup::GetBaseGroup();
        CPrice::Add([
            'PRODUCT_ID'      => $PRODUCT_ID,
            'CATALOG_GROUP_ID'=> $base['ID'],
            'PRICE'           => $price,
            'CURRENCY'        => $currency,
        ]);
    }
}

echo json_encode(['result' => 'ok', 'id' => $PRODUCT_ID], JSON_UNESCAPED_UNICODE);

<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;

header('Content-Type: application/json; charset=utf-8');

$API_KEY = "supersecret123";
$IBLOCK_ID = 1;

if (($_GET['API_KEY'] ?? '') !== $API_KEY) {
    http_response_code(403);
    echo json_encode(["error" => "wrong API_KEY"]);
    exit;
}

if (!Loader::includeModule('iblock')) {
    http_response_code(500);
    echo json_encode(["error" => "iblock not loaded"]);
    exit;
}

$props = [];
$res = CIBlockProperty::GetList([], ["IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y"]);
while ($prop = $res->Fetch()) {
    $item = [
        "ID" => $prop["ID"],
        "CODE" => $prop["CODE"],
        "NAME" => $prop["NAME"],
        "PROPERTY_TYPE" => $prop["PROPERTY_TYPE"], // S, N, L, E
        "USER_TYPE" => $prop["USER_TYPE"],
        "MULTIPLE" => $prop["MULTIPLE"],
    ];

    // если список (L) — подтянем варианты
    if ($prop["PROPERTY_TYPE"] === "L") {
        $enums = [];
        $enumRes = CIBlockPropertyEnum::GetList(["SORT"=>"ASC"], ["PROPERTY_ID"=>$prop["ID"]]);
        while ($enum = $enumRes->Fetch()) {
            $enums[] = [
                "ID" => $enum["ID"],
                "VALUE" => $enum["VALUE"],
                "XML_ID" => $enum["XML_ID"],
                "DEF" => $enum["DEF"],
            ];
        }
        $item["ENUMS"] = $enums;
    }

    $props[] = $item;
}

echo json_encode($props, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

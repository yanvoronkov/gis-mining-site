<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */
/** @global CMain $APPLICATION */

// Упрощенная обертка: только подключаем catalog.section с выбранным шаблоном
$this->setFrameMode(true);

$iblockId = (int) ($arParams["IBLOCK_ID"] ?? 0);
$sectionCode = $arResult["VARIABLES"]["SECTION_CODE"] ?? $arParams["SECTION_CODE"] ?? "";
$smartFilterPath = $arResult["VARIABLES"]["SMART_FILTER_PATH"] ?? "";

// Пытаемся найти ID раздела по коду (только если IBLOCK_ID задан)
$sectionId = 0;
if ($iblockId > 0 && $sectionCode !== "" && CModule::IncludeModule('iblock')) {
    $rsSection = CIBlockSection::GetList(
        [],
        [
            "IBLOCK_ID" => $iblockId,
            "CODE" => $sectionCode,
            "ACTIVE" => "Y",
        ],
        false,
        ["ID"]
    );
    if ($arSection = $rsSection->Fetch()) {
        $sectionId = (int) $arSection["ID"];
    }
}

$sectionTemplate = $arParams["SECTION_TEMPLATE"] ?? ".default";

?>

<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $iblockId ?>">
    <div id="catalog-section-container">
        <?php
        $APPLICATION->IncludeComponent(
            "bitrix:catalog.section",
            $sectionTemplate,
            array_merge($arParams, [
                "SEF_MODE" => "N",
                "SECTION_ID" => $sectionId,
                "SECTION_CODE" => $sectionCode,
                "SHOW_ALL_WO_SECTION" => "Y",
                "SMART_FILTER_PATH" => $smartFilterPath,
                "ADD_SECTIONS_CHAIN" => "Y",
                "SET_TITLE" => "N",
                "SET_BROWSER_TITLE" => "N",
                "SET_META_KEYWORDS" => "N",
                "SET_META_DESCRIPTION" => "N",
            ]),
            $component,
            ["HIDE_ICONS" => "Y"]
        );
        ?>
    </div>
</div>
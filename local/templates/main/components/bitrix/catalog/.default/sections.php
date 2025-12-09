<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */
/** @global CMain $APPLICATION */

// Этот файл отвечает за отображение списка разделов каталога
// Пока не используется, так как у нас прямые переходы в разделы

$this->setFrameMode(true);
$iblockId = $arParams["IBLOCK_ID"];

// Получаем данные инфоблока для описания (по умолчанию ID=1, как в старом файле)
$description = "";
$iblockName = "ASIC-майнеры";
if (CModule::IncludeModule('iblock')) {
    $iblock = CIBlock::GetByID(1)->Fetch();
    if ($iblock) {
        $iblockName = $iblock['NAME'];
        $description = $iblock['DESCRIPTION'];
    }
}
?>

<?php
// H1 берется из свойств страницы (заголовок страницы)
// Настраивается в админке: Свойства папки -> Заголовок
?>

<div class="catalog-page catalog-new container" id="app-root" data-iblock-id="<?= $iblockId ?>">
    <?php
    // H1 теперь выводится в header.php
    $APPLICATION->SetPageProperty("h1_class", "catalog-page__title highlighted-color");
    ?>
    <!-- Компонент живого поиска по каталогу -->
    <?php
    $APPLICATION->IncludeComponent(
        "custom:catalog.search",
        ".default",
        array(
            "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
            "MIN_QUERY_LENGTH" => 2,
            "MAX_RESULTS" => 10,
            "SHOW_PRICE" => "Y",
            "PRICE_CODE" => "BASE",
            "CACHE_TIME" => 3600,
        )
    );
    ?>

    <div class="catalog-page__body">
        <aside class="catalog-page__full">
            <!-- АККОРДЕОН 1: КАТЕГОРИИ (Компонент списка инфоблоков) -->
            <?php
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section.list",
                "category",
                array(
                    "IBLOCK_TYPE" => "catalog",
                    "IBLOCK_ID" => $iblockId,
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                    "CACHE_GROUPS" => "Y",
                ),
                false
            );
            ?>

            <!-- Описание секции -->
            <section class="catalog-about section-padding">
                <div class="about__content">
                    <h2 class="about__title"><?= $iblockName ?></h2>
                    <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                        <?= $description ?: '<p>Описание для этого раздела еще не добавлено.</p>' ?>
                    </div>
                </div>
            </section>

            <!-- Секция "Обратная связь" -->
            <? $APPLICATION->IncludeComponent(
                "custom:feedback.section",
                ".default",
                array()
            ); ?>
        </aside>
    </div>
</div>
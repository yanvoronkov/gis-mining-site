<?php
/**
 * Главная страница каталога
 * Показывает список всех категорий (инфоблоков)
 */

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Каталог оборудования для майнинга");
$APPLICATION->SetPageProperty("header_right_class", "color-block");
$APPLICATION->SetPageProperty("h1_class", "catalog-page__title highlighted-color");

// SEO для главной страницы каталога
$APPLICATION->SetPageProperty("description", "Полный каталог оборудования для майнинга: ASIC-майнеры, видеокарты, ГПУ-станции, инвестиции, контейнеры и готовый бизнес от GIS Mining.");
?>

<div class="catalog-page catalog-new container" id="app-root">
    <!-- Поиск по каталогу -->
    <?php
    $APPLICATION->IncludeComponent(
        "custom:catalog.search",
        ".default",
        [
            "IBLOCK_IDS" => IBLOCK_IDS_ALL_CATALOG,
            "MIN_QUERY_LENGTH" => 2,
            "MAX_RESULTS" => 10,
            "SHOW_PRICE" => "Y",
            "PRICE_CODE" => "BASE",
            "CACHE_TIME" => 3600,
        ]
    );
    ?>

    <div class="catalog-page__body">
        <aside class="catalog-page__full">
            <!-- Список категорий каталога -->
            <?php
            $APPLICATION->IncludeComponent(
                "bitrix:catalog.section.list",
                "category",
                [
                    "IBLOCK_TYPE" => "catalog",
                    "IBLOCK_ID" => IBLOCK_CATALOG_ASICS, // Любой ID, компонент получит все инфоблоки типа catalog
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "3600",
                    "CACHE_GROUPS" => "Y",
                ],
                false
            );
            ?>

            <!-- Описание каталога -->
            <section class="catalog-about section-padding">
                <div class="about__content">
                    <h2 class="about__title">О каталоге GIS Mining</h2>
                    <div class="about__tab-content js-tab-content is-active" data-tab="overview">
                        <p>GIS Mining предлагает полный спектр оборудования и решений для майнинга криптовалют:</p>
                        <ul>
                            <li><strong>ASIC-майнеры</strong> — профессиональное оборудование для добычи Bitcoin и
                                других криптовалют</li>
                            <li><strong>Видеокарты</strong> — мощные GPU для майнинга и других вычислительных задач</li>
                            <li><strong>ГПУ-станции</strong> — газопоршневые электростанции для автономного
                                энергоснабжения</li>
                            <li><strong>Инвестиции</strong> — возможности инвестирования в майнинговую инфраструктуру
                            </li>
                            <li><strong>Контейнеры</strong> — готовые контейнерные решения для размещения оборудования
                            </li>
                            <li><strong>Готовый бизнес</strong> — комплексные решения под ключ</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Секция обратной связи -->
            <? $APPLICATION->IncludeComponent(
                "custom:feedback.section",
                ".default",
                []
            ); ?>
        </aside>
    </div>
</div>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

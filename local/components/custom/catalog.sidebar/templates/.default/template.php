<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */

$IBLOCK_ID = $arResult['IBLOCK_ID'];
$SHOW_FILTER = $arResult['SHOW_FILTER'];
$FILTER_NAME = $arResult['FILTER_NAME'];
$SEF_RULE = $arResult['SEF_RULE'];
$SECTION_ID = $arResult['SECTION_ID'];
?>

<aside class="catalog-page__sidebar">
    <!-- Навигация по инфоблокам -->
    <?php
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.section.list",
        "iblocks_list",
        [
            "IBLOCK_TYPE" => "catalog",
            "IBLOCK_ID" => $IBLOCK_ID,
            "CACHE_TYPE" => "A",
            "CACHE_TIME" => "3600",
            "CACHE_GROUPS" => "Y",
        ],
        false
    );
    ?>

    <!-- Умный фильтр (только для товарных инфоблоков) -->
    <?php if ($SHOW_FILTER): ?>
        <div class="catalog-accordion">
            <button type="button" class="catalog-accordion__toggle btn btn-primary not-mobile-visible">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_3738_35183)">
                        <path
                            d="M0.5 2.82059H8.11266C8.34209 3.86494 9.27472 4.64897 10.387 4.64897C11.4992 4.64897 12.4318 3.86497 12.6613 2.82059H15.5C15.7761 2.82059 16 2.59672 16 2.32059C16 2.04447 15.7761 1.82059 15.5 1.82059H12.661C12.4312 0.77678 11.4972 -0.00775146 10.387 -0.00775146C9.27609 -0.00775146 8.34262 0.776655 8.11284 1.82059H0.5C0.223875 1.82059 0 2.04447 0 2.32059C0 2.59672 0.223875 2.82059 0.5 2.82059ZM9.05866 2.3219C9.05866 2.32012 9.05869 2.31831 9.05869 2.31653C9.06087 1.58631 9.65672 0.99228 10.387 0.99228C11.1162 0.99228 11.7121 1.5855 11.7152 2.31537L11.7153 2.32272C11.7142 3.05419 11.1187 3.649 10.387 3.649C9.65553 3.649 9.06028 3.05478 9.05863 2.32375L9.05866 2.3219ZM15.5 13.1794H12.661C12.4311 12.1356 11.4972 11.351 10.387 11.351C9.27609 11.351 8.34262 12.1355 8.11284 13.1794H0.5C0.223875 13.1794 0 13.4032 0 13.6794C0 13.9555 0.223875 14.1794 0.5 14.1794H8.11266C8.34209 15.2237 9.27472 16.0077 10.387 16.0077C11.4992 16.0077 12.4318 15.2237 12.6613 14.1794H15.5C15.7761 14.1794 16 13.9555 16 13.6794C16 13.4032 15.7761 13.1794 15.5 13.1794ZM10.387 15.0077C9.65553 15.0077 9.06028 14.4135 9.05863 13.6825L9.05866 13.6807C9.05866 13.6789 9.05869 13.6771 9.05869 13.6753C9.06087 12.9451 9.65672 12.351 10.387 12.351C11.1162 12.351 11.7121 12.9442 11.7152 13.6741L11.7153 13.6814C11.7143 14.413 11.1188 15.0077 10.387 15.0077ZM15.5 7.5H7.88734C7.65791 6.45566 6.72528 5.67165 5.61303 5.67165C4.50078 5.67165 3.56816 6.45566 3.33872 7.5H0.5C0.223875 7.5 0 7.72387 0 8C0 8.27615 0.223875 8.5 0.5 8.5H3.33897C3.56888 9.54378 4.50275 10.3283 5.61303 10.3283C6.72391 10.3283 7.65738 9.5439 7.88716 8.5H15.5C15.7761 8.5 16 8.27615 16 8C16 7.72387 15.7761 7.5 15.5 7.5ZM6.94134 7.99869C6.94134 8.0005 6.94131 8.00228 6.94131 8.00406C6.93912 8.73428 6.34328 9.32831 5.61303 9.32831C4.88381 9.32831 4.28794 8.73509 4.28478 8.00525L4.28469 7.99794C4.28578 7.26637 4.88125 6.67165 5.61303 6.67165C6.34447 6.67165 6.93972 7.26584 6.94137 7.9969L6.94134 7.99869Z"
                            fill="white" />
                    </g>
                    <defs>
                        <clipPath id="clip0_3738_35183">
                            <rect width="16" height="16" fill="white" />
                        </clipPath>
                    </defs>
                </svg>
                <span>Фильтры</span>
                <svg class="icon-arrow" width="10" height="5" viewBox="0 0 10 5" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 0.5L5 4.5L1 0.5" stroke="white" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div class="catalog-accordion__content filters">
                <?php
                $APPLICATION->IncludeComponent(
                    "bitrix:catalog.smart.filter",
                    "smart_filter",
                    [
                        "CACHE_GROUPS" => "Y",
                        "CACHE_TIME" => "36000000",
                        "CACHE_TYPE" => "A",
                        "CONVERT_CURRENCY" => "N",
                        "DISPLAY_ELEMENT_COUNT" => "Y",
                        "FILTER_NAME" => $FILTER_NAME,
                        "FILTER_VIEW_MODE" => "vertical",
                        "HIDE_NOT_AVAILABLE" => "N",
                        "IBLOCK_ID" => $IBLOCK_ID,
                        "SECTION_ID" => $SECTION_ID,
                        "IBLOCK_TYPE" => "catalog",
                        "INSTANT_RELOAD" => "N",
                        "SEF_MODE" => "Y",
                        "SEF_RULE" => $SEF_RULE,
                        "SMART_FILTER_PATH" => $_REQUEST["SMART_FILTER_PATH"] ?? "",
                        "COMPONENT_TEMPLATE" => "smart_filter",
                        "POPUP_POSITION" => "left",
                        "COMPONENT_CONTAINER_ID" => "catalog-section-container"
                    ],
                    false
                );
                ?>
            </div>
        </div>
    <?php endif; ?>
</aside>
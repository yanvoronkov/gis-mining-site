<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
    "NAME" => "Автоматические хлебные крошки",
    "DESCRIPTION" => "Автоматически формирует хлебные крошки на основе URL страницы с микроразметкой Schema.org",
    "ICON" => "/images/icon.gif",
    "CACHE_PATH" => "Y",
    "SORT" => 10,
    "PATH" => array(
        "ID" => "content",
        "NAME" => "Контент",
        "CHILD" => array(
            "ID" => "navigation",
            "NAME" => "Навигация",
        ),
    ),
);
?>


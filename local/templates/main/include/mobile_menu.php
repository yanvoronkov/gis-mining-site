<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>

<!-- Мобильное нижнее меню -->
<nav class="mobile-bottom-menu">
  <a href="/catalog/" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/catalog.svg" alt="Каталог" width="48" height="48">
    </span>
    <span class="text">Каталог</span>
  </a>

  <a href="/our-blog/" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/blog.svg" alt="Блог" width="48" height="48">
    </span>
    <span class="text">Блог</span>
  </a>

  <a href="/search/" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/search.svg" alt="Поиск" width="48" height="48">
    </span>
    <span class="text">Поиск</span>
  </a>

  <a href="/contacts/" class="menu-item">
    <span class="icon">
      <img src="<?= SITE_TEMPLATE_PATH ?>/include/mobile_menu/contact.svg" alt="Контакты" width="48" height="48">
    </span>
    <span class="text">Контакты</span>
  </a>
</nav>

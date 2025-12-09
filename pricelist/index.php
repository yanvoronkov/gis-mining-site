<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

// --- ПОДГОТОВКА ДАННЫХ (НАДЕЖНЫЙ СПОСОБ С ИСПОЛЬЗОВАНИЕМ НАСТРОЕК БИТРИКСА) ---

// Определяем протокол
$protocol = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";

// Получаем имя сервера из настроек сайта. Это самый надежный способ.
// Константа SITE_SERVER_NAME определяется на основе поля "URL сервера", которое мы настроили.
$serverName = defined('SITE_SERVER_NAME') && strlen(SITE_SERVER_NAME) > 0 ? SITE_SERVER_NAME : $_SERVER['SERVER_NAME'];

// Получаем чистый URL страницы без GET-параметров
$pageUrl = $APPLICATION->GetCurPage(false);

// Собираем полный канонический URL
$fullPageUrl = $protocol . '://' . $serverName . $pageUrl;

// Используем общую картинку, так как уникальная не была предоставлена.
$ogImageUrl = $protocol . '://' . $serverName . '/local/templates/main/assets/img/home/home_open-graph_image.webp';

// --- ЗАГОЛОВОК И ОСНОВНЫЕ SEO-ТЕГИ ---

$APPLICATION->SetPageProperty("TITLE", "Прайс-лист");
$APPLICATION->SetTitle("Прайс-лист");
// Хлебные крошки теперь формируются автоматически в header
$APPLICATION->SetPageProperty("description", "Прайс-лист коспании GIS-MINING");
$APPLICATION->SetPageProperty("keywords", "");
$APPLICATION->SetPageProperty("robots", "noindex, follow");

// --- OPEN GRAPH МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("OG:TITLE", "Прайс-лист");
$APPLICATION->SetPageProperty("OG:DESCRIPTION", "Прайс-лист компании GIS-MINING");
$APPLICATION->SetPageProperty("OG:TYPE", "profile"); // Для контактов хорошо подходит тип "profile" или "article"
$APPLICATION->SetPageProperty("OG:URL", $fullPageUrl);
$APPLICATION->SetPageProperty("OG:SITE_NAME", "GIS Mining");
$APPLICATION->SetPageProperty("OG:LOCALE", "ru_RU");
$APPLICATION->SetPageProperty("OG:IMAGE", $ogImageUrl);

// --- TWITTER CARD МЕТА-ТЕГИ ---

$APPLICATION->SetPageProperty("TWITTER:CARD", "summary_large_image");
$APPLICATION->SetPageProperty("TWITTER:TITLE", "Прайс-лист");
$APPLICATION->SetPageProperty("TWITTER:DESCRIPTION", "Прайс-лист компании GIS-MINING");
$APPLICATION->SetPageProperty("TWITTER:IMAGE", $ogImageUrl);

// --- СЛУЖЕБНЫЕ СВОЙСТВА (ДЛЯ ВАШЕГО ШАБЛОНА) ---
$APPLICATION->SetPageProperty("main_class", "page-contacts");
$APPLICATION->SetPageProperty("main_class", "page-home");
$APPLICATION->SetPageProperty("header_right_class", "color-block");

// ----- ВЫВОД СКРЫТОЙ МИКРОРАЗМЕТКИ ХЛЕБНЫХ КРОШЕК -----
// Хлебные крошки теперь формируются автоматически в header

// --- Подключаем модули ---
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\HttpClient;

Loader::includeModule('iblock');
Loader::includeModule('catalog');

// === Курсы валют ===
function getCurrencyRates(): array {
  $cache = Cache::createInstance();
  $cacheId = 'daily_currency_rates_v3';
  $cacheDir = '/price_rates/';

  date_default_timezone_set('Europe/Moscow');
  $now = time();
  $todayNoon = strtotime(date('Y-m-d 12:00:00'));
  $nextNoon = ($now < $todayNoon) ? $todayNoon : strtotime('+1 day 12:00:00');
  $ttl = max(60, $nextNoon - $now);

  if ($cache->initCache($ttl, $cacheId, $cacheDir))
    return $cache->getVars();

  elseif ($cache->startDataCache()) {
    $usd = 'N/A';
    $btc = 'N/A';

    // --- USD с ЦБ РФ ---
    try {
      $http = new HttpClient(['timeout' => 10, 'disableSslVerification' => true]);
      $resp = $http->get('https://www.cbr.ru/scripts/XML_daily.asp');
      if ($resp) {
        $xml = @simplexml_load_string($resp);
        foreach ($xml->Valute as $v) {
          if ((string)$v->CharCode === 'USD') {
            $val = floatval(str_replace(',', '.', (string)$v->Value));
            $usd = number_format($val, 2, '.', '');
            break;
          }
        }
      }
    } catch (\Throwable $e) {}

    // --- BTC/USD с Coinbase ---
    try {
      $http = new HttpClient(['timeout' => 10, 'disableSslVerification' => true]);
      $http->setHeader('User-Agent', 'Mozilla/5.0 (compatible; GISMiningBot/1.0)');
      $resp = $http->get('https://api.coinbase.com/v2/prices/BTC-USD/spot');
      if ($http->getStatus() === 200 && $resp) {
        $data = json_decode($resp, true);
        if (isset($data['data']['amount'])) {
          $price = round(floatval($data['data']['amount']));
          $btc = number_format($price, 0, ',', ' ');
        }
      }
    } catch (\Throwable $e) {}

    $result = [
      'USD' => $usd,
      'BTC' => $btc,
      'UPDATED' => date('d.m.Y'),
    ];
    $cache->endDataCache($result);
    return $result;
  }

  return ['USD' => 'N/A', 'BTC' => 'N/A', 'UPDATED' => '—'];
}

$rates = getCurrencyRates();

// === Товары ===
$products = [];
$arSelect = [
  'ID','NAME','DETAIL_PAGE_URL','DETAIL_PICTURE',
  'PROPERTY_POWER',
  'PROPERTY_HASHRATE_TH','PROPERTY_HASHRATE_MH','PROPERTY_HASHRATE_KSOL'
];
$arFilter = ['IBLOCK_ID'=>1,'ACTIVE'=>'Y'];
$res = CIBlockElement::GetList(['NAME'=>'ASC'],$arFilter,false,false,$arSelect);

while($item = $res->GetNext()) {
  $priceData = \CCatalogProduct::GetOptimalPrice($item['ID']);
  if (!$priceData || $priceData['RESULT_PRICE']['DISCOUNT_PRICE'] <= 0) continue;

  $price = number_format($priceData['RESULT_PRICE']['DISCOUNT_PRICE'], 0, ',', ' ');
  $hashrate = '';
  if ($item['PROPERTY_HASHRATE_TH_VALUE'])
    $hashrate = $item['PROPERTY_HASHRATE_TH_VALUE'].' TH/s';
  elseif ($item['PROPERTY_HASHRATE_MH_VALUE'])
    $hashrate = $item['PROPERTY_HASHRATE_MH_VALUE'].' MH/s';
  elseif ($item['PROPERTY_HASHRATE_KSOL_VALUE'])
    $hashrate = $item['PROPERTY_HASHRATE_KSOL_VALUE'].' KSol/s';

  $power = $item['PROPERTY_POWER_VALUE'] ? $item['PROPERTY_POWER_VALUE'].' Вт' : '';

  $imageSrc = '';
if (!empty($item['DETAIL_PICTURE'])) {
  $imageSrc = CFile::GetPath($item['DETAIL_PICTURE']);
} else {
  $imageSrc = SITE_TEMPLATE_PATH . '/assets/img/components/popup_form_image.png'; // запасная
}


  $products[] = [
  'NAME' => $item['NAME'],
  'URL' => $item['DETAIL_PAGE_URL'],
  'PRICE' => (float)$priceData['RESULT_PRICE']['DISCOUNT_PRICE'],
  'PRICE_FORMATTED' => "от {$price} ₽ с НДС",
  'POWER' => $power,
  'HASHRATE' => $hashrate,
  'IMG' => $imageSrc, // 👈 добавляем
];

}
?>

<section class="section-contacts container">
  <h1 class="section-contacts__title section-title highlighted-color">Прайс-лист</h1>

  <!-- ======= Градиентная шапка прайса ======= -->
<div class="price-ribbon">
  <div class="ribbon-title">ПРАЙС-ЛИСТ</div>

  <div class="ribbon-right">
    <div class="ribbon-label">Используемые курсы:</div>

    <!-- BTC -->
    <div class="ribbon-rate">
      <img class="rate-icon" src="./bitcoin.svg" alt="BTC" width="28" height="28" loading="lazy">
      <div class="rate-text">
        <div class="rate-value"><?= htmlspecialcharsbx($rates['BTC']) ?> USD</div>
        <div class="rate-source">www.binance.com</div>
      </div>
    </div>

    <!-- USD -->
    <div class="ribbon-rate">
      <img class="rate-icon" src="./dollar.svg" alt="USD" width="28" height="28" loading="lazy">
      <div class="rate-text">
        <div class="rate-value"><?= htmlspecialcharsbx($rates['USD']) ?> ₽</div>
        <div class="rate-source">www.cbr.ru</div>
      </div>
    </div>
  </div>
</div>
<!-- ======= /Градиентная шапка прайса ======= -->


<div class="hosting-section">
  <div class="hosting-block">
    <img src="./star.svg" alt="Размещение" class="hosting-icon">
    <div class="hosting-text">
      <div class="hosting-text-inner">
        <span>
          Доступно размещение на хостинге в дата-центре на Калининской АЭС
          по тарифу <span class="highlight">5.3 ₽/кВт</span>
        </span>
        <a href="https://gis-mining.ru/razmeschenie/" target="_blank" class="hosting-btn">Подробнее</a>
      </div>
    </div>
  </div>
</div>




<!-- ======= Блок пояснений под тарифом ======= -->
<div class="price-info">
  <div class="info-left">
    <b>В стоимость оборудования входит:</b> Авиа-доставка из Китая, таможенное оформление, доставка,
    установка и настройка оборудования на хостинге.
  </div>

  <div class="info-right">
    * Не является публичной офертой. Цены рассчитываются по курсу на момент оплаты.
  </div>
</div>
<!-- ======= /Блок пояснений ======= -->




  

  <div class="price-top-bar">
    <div class="search-wrapper">
      <i class="fa-solid fa-magnifying-glass search-icon"></i>
      <input type="text" id="searchInput" placeholder="Поиск модели..." />
      <span class="search-spinner" id="searchSpinner"></span>
    </div>

    <div class="price-date">
      Цены актуальны на <b><?= $rates['UPDATED'] ?></b>
    </div>


  </div>

  <div class="price-wrap">
  <div id="tableLoader" class="loader-overlay" aria-hidden="true">
    <div class="loader"></div>
  </div>

  <table id="priceTable" class="price-table fade-in">
    <thead>
      <tr>
        <th class="left">Модель</th>
        <th class="sortable" data-sort="price">Цена <i class="fa-solid fa-sort"></i></th>
        <th>Потребление</th>
        <th>Хешрейт</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td class="left">
            <a href="<?= htmlspecialcharsbx($p['URL']) ?>" target="_blank"><?= htmlspecialcharsbx($p['NAME']) ?></a>
          </td>
          <td class="price" data-value="<?= $p['PRICE'] ?>"><?= $p['PRICE_FORMATTED'] ?></td>
          <td><?= htmlspecialcharsbx($p['POWER']) ?></td>
          <td><?= htmlspecialcharsbx($p['HASHRATE']) ?></td>
          <td>
  <a href="#"
     class="btn-order js-open-popup-form"
     data-metric-goal="open-order"
     data-name="<?= htmlspecialcharsbx($p['NAME']) ?>"
     data-img="<?= htmlspecialcharsbx($p['IMG']) ?>">
    Заказать
  </a>
</td>



        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="./style.css">
  <script src="./js.js"></script>


  <!-- ===== POPUP "ЗАКАЗ" ===== -->
<div class="form-popup popup-form-wrapper" id="mainPopupFormWrapper" style="display: none;">
  <div class="form-popup__items">
    <button type="button" class="form-popup__close-btn popup-form__close-btn menu-close" id="closeMainPopupFormBtn" aria-label="Закрыть">
      <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M22.9844 10L10.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M10.9844 10L22.9844 22" stroke="#6F7682" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <div class="form-popup__title-img-wrapper">
      <h2 class="form-popup__title">Заказ оборудования</h2>
      <div class="form-popup__img-wrapper">
        <img src="<?= SITE_TEMPLATE_PATH ?>/assets/img/components/popup_form_image.png"
             alt="Контейнер для майнинг фермы"
             class="form-popup__img" loading="lazy" width="300" height="200">
      </div>
    </div>

    <form class="form-popup__popup-form js-ajax-form" id="contactFormPopup" data-metric-goal="send-price-lead">
      <p class="form-popup__cta">
        Заполните форму, чтобы оформить заказ. Мы свяжемся с вами в ближайшее время.
      </p>

      <label for="popup_client_name">Имя:</label>
      <input type="text" name="client_name" id="popup_client_name" placeholder="Имя" class="form-popup__input form-input">

      <label for="popup_client_phone">Телефон*:</label>
      <input type="tel" name="client_phone" id="popup_client_phone" placeholder="Телефон*" class="form-popup__input form-input js-phone-mask" required>

      <label for="popup_client_email">Email:</label>
      <input type="email" name="client_email" id="popup_client_email"
             placeholder="your@email.com (необязательно)" class="form-popup__input form-input">

      <input type="hidden" name="source_id" value="23">
      <input type="hidden" name="form_name" value="">
      <input type="hidden" name="page_url" value="">
      <input type="hidden" name="client_comment" id="popup_product_name" value="">


      <div class="form-group form-check mb-3">
        <input type="checkbox" id="privacy-policy-popup" name="privacy-policy" class="form-check-input" required>
        <label for="privacy-policy-popup" class="form-check-label">
          Согласен(а) с <a href="/policy-confidenciales/" target="_blank"><u>политикой конфиденциальности</u></a>
        </label>
      </div>

      <button type="submit" class="form-popup__submit-btn btn btn-primary" id="submitContactBtnPopup">
        Оставить заявку
      </button>

      <p class="form-popup__error-message form-error-message" style="color: red; display: none;"></p>
    </form>
  </div>
</div>
<!-- ===== /POPUP ===== -->

</section>






<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php"); ?>
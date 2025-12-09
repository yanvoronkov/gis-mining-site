<?php

// URL исходного фида
$inputFeedUrl = 'https://gis-mining.ru/bitrix/catalog_export/product-feed-artem.php';
// Имя выходного файла
$outputFeedFilename = 'feed-collections.xml';

/**
 * Функция для загрузки содержимого фида по URL.
 * @param string $url URL фида.
 * @return string|false Содержимое фида или false в случае ошибки.
 */
function fetchFeed($url) {
    $context = stream_context_create([
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (compatible; FeedParser/1.0)\r\n"
        ]
    ]);

    $content = file_get_contents($url, false, $context);
    if ($content === FALSE) {
        echo "Ошибка: Не удалось загрузить фид по адресу $url\n";
        return false;
    }
    return $content;
}

/**
 * Функция для парсинга XML фида и извлечения данных о продуктах.
 * @param string $xmlContent Содержимое XML фида.
 * @return array Массив с данными о продуктах.
 */
function parseProducts($xmlContent) {
    $products = [];
    $dom = new DOMDocument();
    // Подавляем предупреждения, если XML не совсем корректный
    $libxml_previous_state = libxml_use_internal_errors(true);

    if (!$dom->loadXML($xmlContent)) {
        echo "Ошибка: Невозможно распарсить XML фид.\n";
        foreach (libxml_get_errors() as $error) {
            echo "\t", $error->message;
        }
        libxml_clear_errors();
        libxml_use_internal_errors($libxml_previous_state);
        return $products;
    }

    libxml_use_internal_errors($libxml_previous_state);

    $xpath = new DOMXPath($dom);
    // Предполагаем, что каждый товар обернут в тег <offer>
    $offers = $xpath->query('//offer');

    foreach ($offers as $offer) {
        $product = [];

        // Извлечение данных
        $getUrl = $xpath->query('url', $offer);
        $product['url'] = $getUrl->length > 0 ? $getUrl->item(0)->nodeValue : '';

        $getName = $xpath->query('name', $offer);
        $product['name'] = $getName->length > 0 ? $getName->item(0)->nodeValue : '';

        $getCategoryId = $xpath->query('categoryId', $offer);
        $product['category_id'] = $getCategoryId->length > 0 ? $getCategoryId->item(0)->nodeValue : '';

        $getVendor = $xpath->query('vendor', $offer);
        $product['vendor'] = $getVendor->length > 0 ? $getVendor->item(0)->nodeValue : '';

        $getPrice = $xpath->query('price', $offer);
        $product['price'] = $getPrice->length > 0 ? $getPrice->item(0)->nodeValue : '';

        $getCurrency = $xpath->query('currencyId', $offer);
        $product['currency'] = $getCurrency->length > 0 ? $getCurrency->item(0)->nodeValue : '';

        $getPicture = $xpath->query('picture', $offer);
        $product['picture'] = $getPicture->length > 0 ? $getPicture->item(0)->nodeValue : '';

        $products[] = $product;
    }

    return $products;
}

/**
 * Функция для создания нового XML фида в формате коллекций.
 * @param array $products Массив с данными о продуктах.
 * @return string XML-строка нового фида.
 */
function createCollectionFeed($products) {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true; // Для красивого форматирования XML

    $collections = $dom->createElement('collections');
    $dom->appendChild($collections);

    // Получаем уникальные категории (categoryId)
    $categories = [];
    foreach ($products as $product) {
        if (!empty($product['category_id'])) {
            $categories[$product['category_id']] = $product['category_id']; // Используем ID как ключ
        }
    }

    // Создаем элементы <collection> для каждой уникальной категории
    // В данном примере мы создаем одну общую коллекцию "All Products"
    // и по одной для каждого производителя (vendor).
    // Вы можете изменить логику здесь в зависимости от ваших требований к "collection".

    // 1. Коллекция "All Products"
    $collectionAll = $dom->createElement('collection');
    $collections->appendChild($collectionAll);

    $handleAll = $dom->createElement('handle', 'all-products');
    $collectionAll->appendChild($handleAll);

    $titleAll = $dom->createElement('title', 'All Products');
    $collectionAll->appendChild($titleAll);

    $productsListAll = $dom->createElement('products');
    $collectionAll->appendChild($productsListAll);

    foreach ($products as $product) {
        if (!empty($product['url'])) {
             $productElement = $dom->createElement('product');
             $productElement->setAttribute('url', $product['url']);
             $productsListAll->appendChild($productElement);
        }
    }

    // 2. Коллекции по производителям (vendor)
    $vendors = [];
    foreach ($products as $product) {
        if (!empty($product['vendor'])) {
            $vendors[$product['vendor']] = $product['vendor'];
        }
    }

    foreach ($vendors as $vendor) {
         $collectionVendor = $dom->createElement('collection');
         $collections->appendChild($collectionVendor);

         // Создание handle из названия вендора (простая транслитерация и очистка)
         $handleVendor = strtolower(preg_replace('/[^a-zA-Z0-9\-_]/', '-', $vendor));
         $handleVendor = preg_replace('/-+/', '-', $handleVendor); // Убираем повторяющиеся дефисы
         $handleVendor = trim($handleVendor, '-'); // Убираем дефисы в начале и конце

         $handleElement = $dom->createElement('handle', $handleVendor);
         $collectionVendor->appendChild($handleElement);

         $titleElement = $dom->createElement('title', htmlspecialchars($vendor, ENT_XML1, 'UTF-8'));
         $collectionVendor->appendChild($titleElement);

         $productsListVendor = $dom->createElement('products');
         $collectionVendor->appendChild($productsListVendor);

         foreach ($products as $product) {
             if (!empty($product['url']) && isset($product['vendor']) && $product['vendor'] === $vendor) {
                 $productElement = $dom->createElement('product');
                 $productElement->setAttribute('url', $product['url']);
                 $productsListVendor->appendChild($productElement);
             }
         }
    }

    // 3. Коллекции по категориям (categoryId)
    // Для простоты, используем ID категории как handle. В реальном проекте лучше использовать человекочитаемые названия.
     foreach ($categories as $categoryId) {
         $collectionCategory = $dom->createElement('collection');
         $collections->appendChild($collectionCategory);

         $handleCategory = 'category-' . $categoryId;
         $handleElement = $dom->createElement('handle', $handleCategory);
         $collectionCategory->appendChild($handleElement);

         // Заголовок категории можно получить из другого источника, здесь просто ID
         $titleElement = $dom->createElement('title', 'Category ' . $categoryId);
         $collectionCategory->appendChild($titleElement);

         $productsListCategory = $dom->createElement('products');
         $collectionCategory->appendChild($productsListCategory);

         foreach ($products as $product) {
             if (!empty($product['url']) && isset($product['category_id']) && $product['category_id'] === $categoryId) {
                 $productElement = $dom->createElement('product');
                 $productElement->setAttribute('url', $product['url']);
                 $productsListCategory->appendChild($productElement);
             }
         }
    }


    return $dom->saveXML();
}

/**
 * Функция для сохранения XML-строки в файл.
 * @param string $xmlString XML-строка.
 * @param string $filename Имя файла.
 * @return bool True в случае успеха, false в случае ошибки.
 */
function saveFeedToFile($xmlString, $filename) {
    $result = file_put_contents($filename, $xmlString);
    if ($result === false) {
        echo "Ошибка: Не удалось записать фид в файл $filename\n";
        return false;
    }
    return true;
}

// --- Основная логика скрипта ---

echo "Начинаем генерацию фида коллекций...\n";

// 1. Загрузка фида
$xmlContent = fetchFeed($inputFeedUrl);
if ($xmlContent === false) {
    exit(1); // Завершаем скрипт с ошибкой
}

// 2. Парсинг фида
$products = parseProducts($xmlContent);
if (empty($products)) {
    echo "Предупреждение: Не найдено продуктов в фиде или произошла ошибка парсинга.\n";
    // Можно завершить или продолжить с пустым фидом
    // exit(1);
}

// 3. Создание нового фида
echo "Создаём XML фид коллекций...\n";
$newFeedXml = createCollectionFeed($products);

// 4. Сохранение в файл
if (saveFeedToFile($newFeedXml, $outputFeedFilename)) {
    echo "Фид успешно сохранён в файл: $outputFeedFilename\n";
} else {
    exit(1); // Завершаем скрипт с ошибкой
}

echo "Генерация завершена.\n";
?>
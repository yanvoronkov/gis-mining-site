# Инструкция по настройке модуля "SEO умного фильтра Lite" для фильтра CRYPTO=ZEC

## Текущая проблема
URL `/catalog/asics/filter-zec-crypto/` не применяет фильтр по свойству CRYPTO=ZEC

## Анализ текущей настройки

### Как работает модуль:
1. Перехватывает запрос к красивому URL (например, `/catalog/asics/filter-zec-crypto/`)
2. Преобразует его в стандартный URL фильтра Bitrix (например, `/catalog/asics/filter/crypto-is-zec/apply/`)
3. Применяет параметры фильтра к результатам

### Проверка текущих настроек SEF-правил

Из файла `/catalog/asics/index.php`:
```php
$sefUrlTemplates = [
    "smart_filter" => "filter/#SMART_FILTER_PATH#/apply/",
    ...
];
```

Это значит, что стандартный URL фильтра должен быть:
```
/catalog/asics/filter/[FILTER_PATH]/apply/
```

Где `[FILTER_PATH]` - это путь фильтра, например:
- `crypto-is-zec` (для фильтра CRYPTO=ZEC)
- `crypto-is-btc` (для фильтра CRYPTO=BTC)
- `manufacturer-is-bitmain` (для фильтра MANUFACTURER=Bitmain)

## Правильная настройка записи в модуле SEO фильтра

### ШАГ 1: Определение стандартного URL фильтра

Сначала нужно определить, какой **стандартный URL** генерирует Bitrix для фильтра CRYPTO=ZEC.

**Как это сделать:**
1. Откройте каталог ASICS: `/catalog/asics/`
2. В фильтре отметьте галочку "ZEC" в свойстве CRYPTO
3. Нажмите "Показать"
4. Посмотрите URL в адресной строке браузера

Должно быть что-то вроде:
```
/catalog/asics/filter/crypto-is-zec/apply/
```

**ВАЖНО:** Запишите этот URL - он нужен для поля "Старая ссылка"

### ШАГ 2: Настройка записи в инфоблоке "SEO умного фильтра Lite"

Откройте запись "Крипта ZEC" (ID: 99073) и настройте следующие поля:

#### Основные поля:

**1. Название:**
```
Крипта ZEC
```

**2. Символьный код:**
```
zec-crypto
```
❌ НЕ УКАЗЫВАЙТЕ полный путь `/catalog/asics/filter-zec-crypto/`
✅ Только код фильтра: `zec-crypto`

#### Вкладка "Значения свойств":

**3. Старая ссылка (без домена):**
```
/catalog/asics/filter/crypto-is-zec/apply/
```
- Это стандартный URL фильтра Bitrix (который вы определили в ШАГ 1)
- **БЕЗ домена**, только путь от корня
- **С ведущим слэшем** `/`
- **С завершающим слэшем** `/` (если есть в оригинале)

**4. Новая ссылка (без домена):**
```
/catalog/asics/filter-zec-crypto/
```
- Это красивый ЧПУ URL, который вы хотите отображать пользователям
- **БЕЗ домена**, только путь от корня
- **С ведущим слэшем** `/`
- **С завершающим слэшем** `/`

**5. Редирект:**
```
301 Moved permanently
```
- Это обеспечит правильное перенаправление для SEO

**6. Мета-тег robots:**
```
index, follow
```

**7. Генерировать все варианты умных ссылок для смарт фильтра:**
✅ **ВКЛЮЧИТЬ** (поставить галочку)

**8. Индексировать модулем поиска:**
✅ **ВКЛЮЧИТЬ** (поставить галочку)

**9. Добавлять в хлебные крошки:**
✅ **ВКЛЮЧИТЬ** (если нужно)

**10. Добавить в карту сайта:**
✅ **ВКЛЮЧИТЬ** (если нужно)

**11. Родительская хлебная крошка:**
```
(оставить пустым или выбрать родительский раздел)
```

**12. SEO H1, TITLE, DESCRIPTION, KEYWORDS:**
Заполните по необходимости, например:

- **SEO H1:** `ASIC-майнеры для добычи ZEC (Zcash)`
- **TITLE:** `Купить ASIC-майнеры для ZEC — каталог GIS Mining 2025`
- **DESCRIPTION:** `Официальные поставки ASIC-майнеров для добычи Zcash (ZEC) под заказ с оплатой НДС и ГТД РФ.`
- **KEYWORDS:** `asic zec, майнер zcash, купить asic для zec`

### ШАГ 3: Проверка работы модуля

После сохранения записи:

1. **Очистите кеш** в админке Битрикс: Настройки > Производительность > Очистить кеш
2. Откройте URL: `https://ваш-домен.ru/catalog/asics/filter-zec-crypto/`
3. Проверьте:
   - ✅ Страница открывается без 404
   - ✅ Отображаются только товары с CRYPTO=ZEC
   - ✅ URL остаётся `/catalog/asics/filter-zec-crypto/` (не меняется)
   - ✅ В хлебных крошках корректный путь

### ШАГ 4: Отладка (если не работает)

#### Проблема 1: 404 ошибка
**Причина:** Модуль не перехватывает URL

**Решение:**
1. Проверьте, что модуль установлен и активен
2. Проверьте файл `/local/components/bitrix/catalog/component.php` - должен содержать код интеграции модуля
3. Включите в `init.php` логирование:
```php
// В /local/php_interface/init.php добавьте:
AddEventHandler("main", "OnBeforeProlog", function() {
    if (strpos($_SERVER['REQUEST_URI'], 'filter-zec-crypto') !== false) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/seo_filter_debug.log', 
            date('Y-m-d H:i:s') . ' - URL: ' . $_SERVER['REQUEST_URI'] . "\n",
            FILE_APPEND
        );
    }
});
```

#### Проблема 2: Страница открывается, но фильтр не применяется
**Причина:** Неправильный формат "Старой ссылки"

**Решение:**
1. Откройте `/catalog/asics/` в браузере
2. Вручную примените фильтр CRYPTO=ZEC через стандартный интерфейс
3. Скопируйте точный URL из адресной строки
4. Вставьте его в поле "Старая ссылка" записи модуля

#### Проблема 3: Редирект 301 не работает
**Причина:** Неправильная настройка редиректа

**Решение:**
1. Проверьте, что в поле "Редирект" выбрано "301 Moved permanently"
2. Очистите кеш браузера и Битрикс

## Дополнительная информация

### Формат URL фильтра в Bitrix

Стандартный фильтр Bitrix генерирует URL в формате:
```
/catalog/[IBLOCK_CODE]/filter/[PROPERTY_CODE]-[OPERATOR]-[VALUE]/apply/
```

Где:
- `[IBLOCK_CODE]` - символьный код инфоблока (asics, videocard и т.д.)
- `[PROPERTY_CODE]` - код свойства в нижнем регистре (crypto, manufacturer, algorithm)
- `[OPERATOR]` - оператор фильтрации:
  - `is` - равно (=)
  - `from` - от (>=)
  - `to` - до (<=)
  - `not` - не равно (!=)
- `[VALUE]` - значение свойства в нижнем регистре (zec, btc, bitmain)

**Примеры:**
- `/catalog/asics/filter/crypto-is-zec/apply/` - только ZEC
- `/catalog/asics/filter/manufacturer-is-bitmain/apply/` - только Bitmain
- `/catalog/asics/filter/crypto-is-zec/manufacturer-is-bitmain/apply/` - ZEC + Bitmain
- `/catalog/asics/filter/price-from-1000000/apply/` - цена от 1000000

### Множественные фильтры

Если нужно создать ЧПУ для комбинации фильтров, например, ZEC + Bitmain:

**Старая ссылка:**
```
/catalog/asics/filter/crypto-is-zec/manufacturer-is-bitmain/apply/
```

**Новая ссылка:**
```
/catalog/asics/filter-zec-bitmain/
```

**Символьный код:**
```
zec-bitmain
```

## Контрольный список (Checklist)

- [ ] Определён стандартный URL фильтра через браузер
- [ ] В записи модуля поле "Символьный код" = `zec-crypto` (только код)
- [ ] В записи модуля поле "Старая ссылка" = `/catalog/asics/filter/crypto-is-zec/apply/`
- [ ] В записи модуля поле "Новая ссылка" = `/catalog/asics/filter-zec-crypto/`
- [ ] Включена галочка "Генерировать все варианты умных ссылок"
- [ ] Очищен кеш Битрикс
- [ ] URL `/catalog/asics/filter-zec-crypto/` открывается без 404
- [ ] Фильтр применяется корректно (показываются только товары с ZEC)

## Проверка интеграции модуля

Убедитесь, что в следующих файлах есть код интеграции:

### 1. `/local/components/bitrix/catalog/component.php`
Должен содержать:
```php
use \Dwstroy\SeoChpuLite\Helper;
use \Dwstroy\SeoChpuLite\Main;
```

### 2. `/local/templates/main/components/bitrix/catalog/tech_catalog/result_modifier.php`
Должен содержать:
```php
if( \Bitrix\Main\Loader::includeModule('dwstroy.seochpulite') ){
    \Dwstroy\SeoChpuLite\Helper::returnUrl();
}
```

### 3. `/local/templates/main/components/bitrix/catalog.smart.filter/smart_filter/result_modifier.php`
Должен содержать:
```php
if( \Bitrix\Main\Loader::includeModule('dwstroy.seochpulite') ){
    \Dwstroy\SeoChpuLite\Helper::returnNewUrl($arParams, $arResult);
}
```

---

**Если после выполнения всех шагов фильтр всё ещё не работает, предоставьте:**
1. Точный URL из адресной строки после применения фильтра CRYPTO=ZEC
2. Скриншот всех полей формы записи "Крипта ZEC" после корректировки
3. Содержимое файла `/upload/seo_filter_debug.log` (если включили логирование)

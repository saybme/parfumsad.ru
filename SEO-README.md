# SEO Разметка для parfumsad.ru

## Обзор

Шаблон сайта теперь включает полную SEO разметку с поддержкой Schema.org (JSON-LD), Open Graph, Twitter Cards и расширенными meta-тегами.

## Установленные компоненты

### 1. Базовые Meta-теги (site/head.htm)

#### SEO основные теги:
- `charset`, `viewport`, `language`
- `title` (динамический с fallback)
- `description`, `keywords`, `author`
- `robots`, `googlebot`, `bingbot`
- `canonical` URL

#### Open Graph (Facebook):
- `og:type`, `og:url`, `og:title`, `og:description`
- `og:image` (с размерами 1200x630)
- `og:locale`, `og:site_name`

#### Twitter Cards:
- `twitter:card` (summary_large_image)
- `twitter:url`, `twitter:title`, `twitter:description`, `twitter:image`

#### Производительность:
- DNS prefetch для Google Fonts
- Preconnect для внешних ресурсов
- Preload для критических CSS файлов

### 2. JSON-LD Schema.org разметка

Все схемы находятся в `themes/tmp/partials/meta/`:

#### Organization & WebSite (site/head.htm)
Базовая разметка организации и функция поиска по сайту.

#### BreadcrumbList (breadcrumb-schema.htm)
**Использование:** Передайте массив `breadcrumbs` со страницы:
```twig
{% set breadcrumbs = [
    {title: 'Главная', url: 'home'|page},
    {title: 'Каталог', url: 'catalog'|page},
    {title: 'Парфюмерия', url: this.page.url}
] %}
```

#### Product (product-schema.htm)
**Использование:** Передайте объект `product` для страницы товара:
```twig
{% set product = {
    name: 'Название товара',
    description: 'Описание...',
    image: 'url-изображения',
    sku: 'SKU123',
    brand: 'Бренд',
    price: 1999.99,
    in_stock: true,
    rating: 4.5,
    review_count: 10
} %}
```

#### Article (article-schema.htm)
**Использование:** Передайте объект `article` для блога/статей:
```twig
{% set article = {
    title: 'Заголовок статьи',
    description: 'Краткое описание...',
    image: 'url-изображения',
    published_at: post.created_at,
    updated_at: post.updated_at,
    author: 'Имя автора'
} %}
```

#### FAQPage (faq-schema.htm)
**Использование:** Передайте массив `faq`:
```twig
{% set faq = [
    {question: 'Вопрос 1?', answer: 'Ответ 1'},
    {question: 'Вопрос 2?', answer: 'Ответ 2'}
] %}
```

## Использование на страницах

### Пример страницы товара:
```twig
title = "Название товара"
url = "/product/:slug"
layout = "default"

[productDetails]
==
{% set product = {
    name: productDetails.name,
    description: productDetails.description,
    image: productDetails.images.first.path,
    sku: productDetails.sku,
    brand: productDetails.brand.name,
    price: productDetails.price,
    in_stock: productDetails.quantity > 0,
    rating: productDetails.reviews_avg_rating,
    review_count: productDetails.reviews_count
} %}

{% set breadcrumbs = [
    {title: 'Главная', url: 'home'|page},
    {title: productDetails.category.name, url: productDetails.category.url},
    {title: productDetails.name, url: this.page.url}
] %}

<div class="product-page">
    <!-- Содержимое страницы -->
</div>
```

### Пример страницы FAQ:
```twig
title = "Часто задаваемые вопросы"
url = "/faq"
layout = "default"
==
{% set faq = [
    {
        question: 'Как оформить заказ?',
        answer: 'Выберите товар, добавьте в корзину...'
    },
    {
        question: 'Какие способы оплаты?',
        answer: 'Мы принимаем оплату...'
    }
] %}

<div class="faq-page">
    <!-- Содержимое страницы -->
</div>
```

## Настройка meta-тегов на странице

В October CMS вы можете установить meta-теги через интерфейс или в коде страницы:

```twig
[viewBag]
meta_title = "Заголовок страницы"
meta_description = "Описание страницы для поисковиков"
meta_keywords = "ключевые, слова, через, запятую"
meta_image = "/path/to/image.jpg"
==
```

## Рекомендации

1. **Всегда заполняйте meta_description** - он используется в Open Graph и Twitter Cards
2. **Используйте качественные изображения** размером 1200x630px для `meta_image`
3. **Canonical URL** автоматически генерируется из текущего адреса страницы
4. **JSON-LD схемы** активируются только при наличии соответствующих переменных

## Проверка SEO

Используйте эти инструменты для проверки разметки:

- [Google Rich Results Test](https://search.google.com/test/rich-results)
- [Schema.org Validator](https://validator.schema.org/)
- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)

## Дополнительные улучшения

Для еще лучшего SEO рассмотрите:

1. Создание `sitemap.xml`
2. Добавление `robots.txt`
3. Настройка Google Analytics / Яндекс.Метрика
4. Оптимизация скорости загрузки (сжатие изображений, минификация)
5. HTTPS сертификат
6. Мобильная адаптивность (уже есть viewport meta)

## Структура файлов

```
themes/tmp/
├── layouts/
│   └── default.htm          # Основной шаблон (подключает все схемы)
└── partials/
    ├── site/
    │   └── head.htm         # Базовые meta-теги, OG, Twitter, Organization schema
    └── meta/
        ├── breadcrumb-schema.htm  # Схема хлебных крошек
        ├── product-schema.htm     # Схема товара
        ├── article-schema.htm     # Схема статьи
        └── faq-schema.htm         # Схема FAQ
```

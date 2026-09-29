# Ссылки и параметры

## Базовый путь

```php
$paginator = (new Paginator(perPage: 20))->path('/users');
```

## Дополнительные query-параметры

```php
$paginator = (new Paginator(perPage: 20))
    ->path('/users')
    ->appends(['status' => 'active']);
```

## Фрагмент

```php
$paginator = (new Paginator(perPage: 20))
    ->path('/users')
    ->fragment('list');
```

## Окно ссылок

```php
$paginator = (new Paginator(perPage: 20))->window(2);
```

`window` — это количество страниц вокруг текущей, которые будут показаны в `meta.links`.

## Параметр страницы

Имя параметра задаётся в резолвере: он читает по нему номер страницы, и `Paginator` строит ссылки с тем же именем.

```php
$paginator = new Paginator(perPage: 20, resolver: new RequestPaginationContextResolver($request, pageParam: 'p'));
```

`Paginator::pageParam()` переопределяет имя только для ссылок — используйте его без резолвера.

## Номер страницы за пределами

- Страница за последней отдаёт пустые данные с `from = to = 0`; «назад» ведёт на последнюю страницу, окно номеров
  строится вокруг неё.
- `RequestPaginationContextResolver` ограничивает номер страницы (`pageMax`, по умолчанию 1 000 000), а `Paginator` —
  так, чтобы смещение помещалось в int: `?page=9223372036854775807` не приводит к ошибке.

## PSR Request

Если путь не задан вручную, можно получить его из PSR Request через резолвер:

```php
use PhpSoftBox\Pagination\RequestPaginationContextResolver;
use Psr\Http\Message\ServerRequestInterface;

$resolver = new RequestPaginationContextResolver($request);

$paginator = (new Paginator(perPage: 20))
    ->resolver($resolver);
```

Можно передать резолвер сразу в конструктор:

```php
$paginator = new Paginator(perPage: 20, resolver: $resolver);
```

Query-параметры из резолвера автоматически попадают в ссылки.
`appends()` можно использовать для дополнения или перезаписи параметров.

## perPage из query

По умолчанию отключено. Включается через резолвер:

```php
$resolver = new RequestPaginationContextResolver(
    $request,
    perPageParam: 'per_page',
    perPageMax: 100,
);

$paginator = (new Paginator(perPage: 20))->resolver($resolver);
```

# Использование

```php
use PhpSoftBox\Pagination\Paginator;

$paginator = new Paginator(perPage: 20);

$result = $paginator->make(
    items: $items,
    total: 120,
    page: 2,
);
```

По умолчанию резолвер не читает `perPage` из query-параметров. Чтобы разрешить это,
передайте `perPageParam` (и, при необходимости, `perPageMax`).

## Настройка без DI

```php
use PhpSoftBox\Pagination\Paginator;
use PhpSoftBox\Pagination\RequestPaginationContextResolver;

$resolver = new RequestPaginationContextResolver($request, perPageParam: 'per_page', perPageMax: 100);

$paginator = new Paginator(perPage: 20, resolver: $resolver);
```

## Настройка через DI

`RequestPaginationContextResolver` зависит от текущего запроса, поэтому не регистрируйте его и `Paginator` с ним как
синглтоны контейнера: в долгоживущем процессе все запросы получили бы резолвер первого. Создавайте их на запрос —
например, фабрикой, которой передаётся запрос:

```php
use PhpSoftBox\Pagination\Paginator;
use PhpSoftBox\Pagination\RequestPaginationContextResolver;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PaginatorFactory
{
    public function forRequest(ServerRequestInterface $request): Paginator
    {
        return new Paginator(
            perPage: 20,
            resolver: new RequestPaginationContextResolver($request, perPageParam: 'per_page', perPageMax: 100),
        );
    }
}

// В контроллере:
$paginator = $paginatorFactory->forRequest($request);
```

Результат:

```json
{
  "data": [],
  "links": {
    "first": "/users?page=1",
    "last": "/users?page=6",
    "prev": "/users?page=1",
    "next": "/users?page=3"
  },
  "meta": {
    "current_page": 2,
    "from": 21,
    "last_page": 6,
    "links": [],
    "path": "/users",
    "per_page": 20,
    "to": 40,
    "total": 120
  }
}
```

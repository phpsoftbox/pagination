<?php

declare(strict_types=1);

namespace PhpSoftBox\Pagination\Tests;

use PhpSoftBox\Pagination\PaginationResult;
use PhpSoftBox\Pagination\Paginator;
use PhpSoftBox\Pagination\RequestPaginationContextResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

use function array_column;
use function array_slice;

use const PHP_INT_MAX;

#[CoversClass(Paginator::class)]
#[CoversClass(PaginationResult::class)]
#[CoversClass(RequestPaginationContextResolver::class)]
#[CoversMethod(Paginator::class, 'make')]
final class PaginationEdgeCasesTest extends TestCase
{
    /**
     * Проверим, что страница за последней пуста без from > to, «назад» ведёт на последнюю, а номера страниц остаются.
     *
     * @see Paginator::make()
     */
    #[Test]
    public function pageBeyondLastIsConsistent(): void
    {
        $meta = new Paginator(perPage: 10)->path('/users')->make([], total: 25, page: 9)->meta();

        self::assertSame(0, $meta['from']);
        self::assertSame(0, $meta['to']);
        self::assertSame('/users?page=3', $meta['links'][0]['url']);
        self::assertSame(['1', '2', '3'], array_column(array_slice($meta['links'], 1, -1), 'label'));
    }

    /**
     * Проверим, что огромный номер страницы из запроса ограничивается и не приводит к ошибке.
     *
     * @see RequestPaginationContextResolver::page()
     */
    #[Test]
    public function hugePageIsCapped(): void
    {
        $resolver = new RequestPaginationContextResolver($this->request(['page' => (string) PHP_INT_MAX]));

        $meta = new Paginator(perPage: 50, resolver: $resolver)->make([], total: 10)->meta();

        self::assertSame(RequestPaginationContextResolver::DEFAULT_PAGE_MAX, $meta['current_page']);
    }

    /**
     * Проверим, что ссылки строятся с тем же именем параметра, по которому резолвер читает страницу.
     *
     * @see Paginator::make()
     */
    #[Test]
    public function linksUseResolverPageParam(): void
    {
        $resolver = new RequestPaginationContextResolver($this->request(['p' => '2']), pageParam: 'p');

        $links = new Paginator(perPage: 10, resolver: $resolver)->make([], total: 30)->links();

        self::assertSame('/users?p=3', $links['next']);
    }

    /**
     * @param array<string, string> $query
     */
    private function request(array $query): ServerRequestInterface
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')->willReturn('/users');
        $uri->method('getFragment')->willReturn('');

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);
        $request->method('getQueryParams')->willReturn($query);

        return $request;
    }
}

# YouCan Liquid

The Liquid engine we use to render themes on YouCan. If you're building a theme, you probably want the [theme docs](https://developer.youcan.shop/themes/introduction) instead.

## Install

```sh
composer require youcanshop/liquid
```

Needs PHP 8.0+.

## Usage

```php
use YouCan\Liquid\Template;

$template = new Template();
$template->parse('Hello, {{ customer.name }}!');

echo $template->render(['customer' => ['name' => 'Ada']]);
```

Custom tags and filters:

```php
$template->registerTag('section', SectionTag::class);
$template->registerFilter('money', fn($amount) => number_format($amount, 2));
```

## Drops

Drops are objects whose fields only load when a template reads them:

```php
use YouCan\Liquid\Drop;

class ProductDrop extends Drop
{
    public function __construct(private Product $product) {}

    public function name(): string
    {
        return $this->product->name;
    }

    public function reviews(): array
    {
        return Review::forProduct($this->product->id);
    }
}
```

For dynamic keys (e.g. a lookup by handle) override `hasKey()` and `invokeDrop()`.

## Differences from standard Liquid

- `first` and `last` work mid-path: `collection.products.first.name`
- quoted, numeric and variable keys: `collections['all']`, `products[0]`, `all_products[handle]`
- `map`, `sort` and `where` read drop fields
- drops are always truthy, and compare by value when they implement `JsonSerializable`

## Development

```sh
composer test
vendor/bin/php-cs-fixer fix --dry-run --diff
```

To release, run `./release.sh -p` (or `-m`, `-M`), then create the GitHub release for the new tag with `gh release create <tag> --generate-notes`.

## Credits

Originally based on [kalimatas/php-liquid](https://github.com/kalimatas/php-liquid).

## License

MIT

# YouCan Liquid

The Liquid template engine that renders YouCan themes, in PHP.

Theme developers write Liquid, not PHP. The theme docs are at [developer.youcan.shop/themes](https://developer.youcan.shop/themes/introduction).

## Install

Requires PHP 8.0 or later.

```sh
composer require youcanshop/liquid
```

## Usage

```php
use YouCan\Liquid\Template;

$template = new Template();
$template->parse('Hello, {{ customer.name }}!');

echo $template->render(['customer' => ['name' => 'Ada']]);
```

A parsed template can be rendered many times with different data.

Custom tags and filters are registered on the template:

```php
$template->registerTag('section', SectionTag::class);
$template->registerFilter('money', fn($amount) => number_format($amount, 2));
```

## Drops

A drop is an object that computes its fields when a template reads them. A field that is never read is never loaded.

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

`hasKey()` and `invokeDrop()` can be overridden for drops whose keys are dynamic, such as a lookup by handle.

## Behavior

On top of standard Liquid:

- `first` and `last` work inside a path: `collection.products.first.name`.
- Keys can be quoted, numeric or variables: `collections['all']`, `products[0]`, `all_products[handle]`.
- `map`, `sort` and `where` read fields of drops and of objects with `toLiquid()`.
- Two drops compare equal when their values are equal, which needs the drop to implement `JsonSerializable`. Every drop is truthy.

## Development

```sh
composer test
vendor/bin/php-cs-fixer fix --dry-run --diff
```

Releases are git tags. `./release.sh -p`, `-m` or `-M` tags the next patch, minor or major version and pushes only that tag. Packagist picks the tag up, and a GitHub release is created for it with `gh release create <tag> --generate-notes`.

## Credits

This library started as a fork of [kalimatas/php-liquid](https://github.com/kalimatas/php-liquid).

## License

MIT, see [LICENSE](LICENSE).

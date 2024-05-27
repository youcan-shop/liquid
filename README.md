# Liquid template engine for PHP [![CI](https://github.com/kalimatas/php-liquid/actions/workflows/tests.yaml/badge.svg)](https://github.com/kalimatas/php-liquid/actions/workflows/tests.yaml) [![Coverage Status](https://coveralls.io/repos/github/kalimatas/php-liquid/badge.svg?branch=master)](https://coveralls.io/github/kalimatas/php-liquid?branch=master) [![Total Downloads](https://poser.pugx.org/liquid/liquid/downloads.svg)](https://packagist.org/packages/liquid/liquid)

A PHP version of Ruby's Liquid Template Engine for YouCan Shop theme development.<br> Liquid allows you to create flexible and dynamic themes for [e-commerce stores](https://youcan.shop/en).

## Why Use Liquid?

- Seperate compiling and rendering stages for improved performance.
- Simple syntax for creating dynamic templates.
- Create reusable components


## Installation

Install via Composer:

```sh
composer require liquid/liquid
```

### Example Usage 

```php
require 'vendor/autoload.php';
use Liquid\Template;

$template = new Template();
$template->parse('Hello, {{ name }}!');
echo $template->render(['name' => 'world']);
```

## Creating YouCan Themes 

Liquid uses a combination of [objects](https://developer.youcan.shop/themes/objects/introduction), [tags](https://developer.youcan.shop/themes/tags/if), and [filters](https://developer.youcan.shop/themes/filters/currency/money) inside template files to display dynamic content.


### What does it look like?

  ```liquid
    {% if user %}
      <p>Welcome back, {{ user.name }}!</p> <!-- Outputs a welcome message if the user is logged in -->
    {% else %}
      <p>Welcome to our store!</p> <!-- Outputs a generic welcome message if the user is not logged in -->
    {% endif %}
  </header>

  <main>
    <h1>{{ product.title }}</h1> <!-- Outputs the product title -->
    <p>{{ product.description }}</p> <!-- Outputs the product description -->
    <p>Price: {{ product.price | money }}</p> <!-- Outputs the product price formatted as money -->
  </main>
```

1. **Output Tags `({{ }})`**: used to display content.
2. **Logic Tags `({% %})`**: used to perform actions such as conditions and loops. 
3. **Filters `(|)`**: used to format the output of variables 
4. **Variables**: used to store data that can be rendered in templates.



For more information, visit the [YouCan Theme Documentation](https://developer.youcan.shop/themes/introduction).